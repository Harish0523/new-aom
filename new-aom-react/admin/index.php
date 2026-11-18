<?php
require_once __DIR__ . '/includes/auth.php';
if (!function_exists('require_admin_login')) {
  http_response_code(500);
  exit('Authentication configuration error.');
}
require_admin_login();
header('Content-Type: text/html; charset=utf-8');

$bookingsCount = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$pendingBookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$contactsCount = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$cmsReady = true;
$upcomingEvents = 0;
$publishedArticles = 0;
$activeAds = 0;
$donationTotal = 0.0;
$contentCount = 0;
try {
    $contentCount = (int) $pdo->query("SELECT COUNT(*) FROM cms_content WHERE status = 'published'")->fetchColumn();
    $rows = $pdo->query("SELECT content_type, payload_json FROM cms_content WHERE status = 'published'")->fetchAll();
    $today = date('Y-m-d');
    foreach ($rows as $row) {
        $data = json_decode((string) $row['payload_json'], true);
        if (!is_array($data)) {
            continue;
        }
        if ($row['content_type'] === 'events') {
            $eventDate = trim((string) ($data['date'] ?? ''));
            $timestamp = $eventDate !== '' ? strtotime($eventDate) : false;
            if ($timestamp === false || $timestamp >= strtotime($today)) {
                $upcomingEvents++;
            }
        } elseif ($row['content_type'] === 'knowledge') {
            $publishedArticles++;
        } elseif ($row['content_type'] === 'crowdfunding') {
            $donationTotal += (float) ($data['raised'] ?? 0);
        } elseif ($row['content_type'] === 'ads') {
            $start = trim((string) ($data['startDate'] ?? ''));
            $end = trim((string) ($data['endDate'] ?? ''));
            $live = !(preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && $start > $today)
                && !(preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) && $end < $today);
            if ($live) {
                $activeAds++;
            }
        }
    }
} catch (PDOException $error) {
    if ($error->getCode() !== '42S02') {
        throw $error;
    }
    $cmsReady = false;
}
$visitorCount = 0;
try {
    $visitorCount = (int) $pdo->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
} catch (PDOException $error) {
    // Visitor tracking starts collecting after the first public page view.
}

$isModerator = $_SESSION['admin_role'] === 'Moderator';

$recentBookings = [];
$recentMessages = [];
if (!$isModerator) {
    $recentBookings = $pdo->query('SELECT full_name, service_name, preferred_date, preferred_time, status, created_at FROM bookings ORDER BY created_at DESC LIMIT 4')->fetchAll();
    $recentMessages = $pdo->query('SELECT name, subject, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 4')->fetchAll();
}

$timeAgo = static function (string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' min ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' hr ago';
    }
    if ($diff < 86400 * 7) {
        return floor($diff / 86400) . ' d ago';
    }
    return date('d M Y', strtotime($datetime));
};

$dashIcon = static function (string $name): string {
    $paths = [
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'calendar-check' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/>',
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'book-open' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'layers' => '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'quote' => '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>',
        'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'sliders' => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};

$cards = [
    ['title' => 'Total Visitors', 'value' => $visitorCount, 'link' => 'reports.php', 'icon' => 'eye', 'sub' => 'site visits tracked'],
    ['title' => 'Total Bookings', 'value' => $bookingsCount, 'link' => 'bookings.php', 'icon' => 'calendar', 'sub' => $pendingBookings . ' pending'],
    ['title' => 'Upcoming Events', 'value' => $upcomingEvents, 'link' => 'content_manager.php?type=events', 'icon' => 'calendar-check', 'sub' => 'scheduled ahead'],
    ['title' => 'Total Donations', 'value' => number_format($donationTotal), 'link' => 'content_manager.php?type=crowdfunding', 'icon' => 'heart', 'sub' => 'LKR raised across projects'],
    ['title' => 'Published Articles', 'value' => $publishedArticles, 'link' => 'content_manager.php?type=knowledge', 'icon' => 'book-open', 'sub' => 'knowledge centre'],
    ['title' => 'Active Advertisements', 'value' => $activeAds, 'link' => 'content_manager.php?type=ads', 'icon' => 'megaphone', 'sub' => 'currently live'],
    ['title' => 'Contact Messages', 'value' => $contactsCount, 'link' => 'contacts.php', 'icon' => 'mail', 'sub' => 'received in inbox'],
    ['title' => 'Pending Bookings', 'value' => $pendingBookings, 'link' => 'bookings.php', 'icon' => 'clock', 'sub' => 'awaiting review'],
];
$dashModules = [
    'homepage' => 'Homepage', 'about' => 'About', 'services' => 'Services',
    'knowledge' => 'Knowledge Centre', 'spiritual-wisdom' => 'Spiritual Wisdom',
    'gallery' => 'Gallery', 'ads' => 'Advertisements', 'events' => 'Workshops & Events',
    'crowdfunding' => 'Crowdfunding', 'testimonials' => 'Testimonials', 'faq' => 'FAQ',
    'translations' => 'Languages & Translations',
];
$moduleIcons = [
    'homepage' => 'home', 'about' => 'info', 'services' => 'layers',
    'knowledge' => 'book-open', 'spiritual-wisdom' => 'sun', 'gallery' => 'image',
    'ads' => 'megaphone', 'events' => 'calendar-check', 'crowdfunding' => 'heart',
    'testimonials' => 'quote', 'faq' => 'help', 'translations' => 'globe',
];
if ($_SESSION['admin_role'] === 'Super Admin') {
    $dashModules['settings'] = 'Website Settings';
    $moduleIcons['settings'] = 'sliders';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NEW AOM — Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <div class="admin-shell">
    <div class="admin-page-header">
      <div>
        <h4>Dashboard</h4>
        <p class="admin-subtitle">
          Welcome back, <?= htmlspecialchars($_SESSION['admin_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($_SESSION['admin_role'], ENT_QUOTES, 'UTF-8') ?>) — here's what's happening on the site.
        </p>
      </div>
    </div>

    <div class="row g-3">
      <?php foreach ($cards as $c): ?>
        <div class="col-6 col-md-4 col-xl-3">
          <a class="admin-stat" href="<?= htmlspecialchars($c['link'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="admin-stat-icon"><?= $dashIcon($c['icon']) ?></span>
            <span class="admin-stat-body">
              <span class="admin-stat-title"><?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="admin-stat-value"><?= htmlspecialchars((string) $c['value'], ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <span class="admin-stat-sub"><?= htmlspecialchars($c['sub'], ENT_QUOTES, 'UTF-8') ?> &rarr;</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!$cmsReady): ?>
      <div class="alert alert-warning mt-4">
        CMS content storage is not installed yet.
        <?php if ($_SESSION['admin_role'] === 'Super Admin'): ?><a href="setup_cms.php" class="alert-link">Run the CMS setup</a>.<?php endif; ?>
      </div>
    <?php else: ?>
    <?php if (!$isModerator): ?>
    <div class="row g-3 mt-4">
      <div class="col-lg-7">
        <div class="admin-panel">
          <div class="admin-panel-head">
            <h6>Latest booking requests</h6>
            <a href="bookings.php" class="admin-link small">View all &rarr;</a>
          </div>
          <?php if (!$recentBookings): ?>
            <p class="admin-panel-empty">No booking requests yet.</p>
          <?php else: ?>
            <?php foreach ($recentBookings as $booking): ?>
              <?php $badgeClass = ['Pending' => 'bg-warning', 'Approved' => 'bg-success', 'Rejected' => 'bg-danger'][$booking['status']] ?? 'bg-secondary'; ?>
              <div class="admin-recent-item">
                <div class="admin-recent-main">
                  <strong><?= htmlspecialchars($booking['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span><?= htmlspecialchars($booking['service_name'], ENT_QUOTES, 'UTF-8') ?> · <?= date('d M Y', strtotime($booking['preferred_date'])) ?>, <?= htmlspecialchars($booking['preferred_time'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="admin-panel">
          <div class="admin-panel-head">
            <h6>Latest messages</h6>
            <a href="contacts.php" class="admin-link small">View all &rarr;</a>
          </div>
          <?php if (!$recentMessages): ?>
            <p class="admin-panel-empty">No contact messages yet.</p>
          <?php else: ?>
            <?php foreach ($recentMessages as $message): ?>
              <div class="admin-recent-item">
                <div class="admin-recent-main">
                  <strong><?= htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <span class="admin-recent-time"><?= htmlspecialchars($timeAgo($message['created_at']), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
      <div><h5 class="fw-bold mb-1">Website content</h5><p class="text-muted small mb-0">Manage drafts, translations, and published sections.</p></div>
    </div>
    <div class="row g-2">
      <?php foreach ($dashModules as $type => $label): ?>
        <?php if ($isModerator && !in_array($type, ['testimonials', 'faq'], true)) continue; ?>
        <div class="col-6 col-md-4 col-xl-3">
          <a class="admin-module" href="content_manager.php?type=<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">
            <span class="admin-module-icon"><?= $dashIcon($moduleIcons[$type] ?? 'info') ?></span>
            <span class="flex-fill"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="text-muted">&rarr;</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>