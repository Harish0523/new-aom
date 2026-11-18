<?php
if (!function_exists('csrf_token')) {
    return;
}
$adminRole = $_SESSION['admin_role'] ?? '';
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$activeFile = basename($_SERVER['PHP_SELF'] ?? '');
$isSuper = $adminRole === 'Super Admin';
$isModerator = $adminRole === 'Moderator';

$initials = '';
foreach (preg_split('/\s+/', trim($adminName)) ?: [] as $namePart) {
    if ($namePart !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(substr($namePart, 0, 1));
    }
}

$icon = static function (string $name): string {
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'content' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'bookings' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'donations' => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'contacts' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'reports' => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
        'security' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu' => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};

$contentType = (string) ($_GET['type'] ?? 'homepage');
if ($contentType === '') {
    $contentType = 'homepage';
}
$contentModules = [
    'homepage' => 'Homepage Management',
    'about' => 'About Us',
    'services' => 'Services',
    'knowledge' => 'Knowledge Centre',
    'spiritual-wisdom' => 'Spiritual Wisdom',
    'gallery' => 'Gallery',
    'ads' => 'Advertisement Management',
    'events' => 'Workshops & Events',
    'crowdfunding' => 'Crowdfunding Projects',
    'testimonials' => 'Testimonials',
    'faq' => 'FAQ Management',
    'translations' => 'Languages & Translations',
];
if ($isSuper) {
    $contentModules['settings'] = 'Website Settings';
}

$navSections = [
    'Main' => [
        ['label' => 'Dashboard', 'href' => 'index.php', 'file' => 'index.php', 'icon' => 'dashboard'],
    ],
    'Website Content' => [],
    'Operations' => [
        ['label' => 'Online Booking', 'href' => 'bookings.php', 'file' => 'bookings.php', 'icon' => 'bookings'],
    ],
    'System' => [],
];
foreach ($contentModules as $moduleType => $moduleLabel) {
    if ($isModerator && !in_array($moduleType, ['testimonials', 'faq'], true)) {
        continue;
    }
    $navSections['Website Content'][] = [
        'label' => $moduleLabel,
        'href' => 'content_manager.php?type=' . rawurlencode($moduleType),
        'file' => 'content_manager.php',
        'icon' => 'content',
        'type' => $moduleType,
    ];
}
if (!$isModerator) {
    $navSections['Operations'][] = ['label' => 'Donations & Bank/QR', 'href' => 'donations.php', 'file' => 'donations.php', 'icon' => 'donations'];
    $navSections['Operations'][] = ['label' => 'Contact Messages', 'href' => 'contacts.php', 'file' => 'contacts.php', 'icon' => 'contacts'];
}
if ($isSuper) {
    $navSections['System'][] = ['label' => 'Users & Roles', 'href' => 'users.php', 'file' => 'users.php', 'icon' => 'users'];
    $navSections['System'][] = ['label' => 'Reports', 'href' => 'reports.php', 'file' => 'reports.php', 'icon' => 'reports'];
    $navSections['System'][] = ['label' => 'Security Logs', 'href' => 'security_logs.php', 'file' => 'security_logs.php', 'icon' => 'security'];
    $navSections['System'][] = ['label' => 'CMS Setup', 'href' => 'setup_cms.php', 'file' => 'setup_cms.php', 'icon' => 'database'];
}

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<input type="checkbox" id="admin-nav-toggle" class="admin-nav-toggle" aria-hidden="true">
<label for="admin-nav-toggle" class="admin-nav-burger" aria-label="Open admin navigation"><?= $icon('menu') ?></label>
<label for="admin-nav-toggle" class="admin-nav-scrim" aria-hidden="true"></label>
<aside class="admin-sidebar">
  <a class="admin-brand" href="index.php">
    <span class="admin-brand-mark">N</span>
    <span class="admin-brand-text">
      <strong>NEW AOM</strong>
      <small>Admin Console</small>
    </span>
  </a>
  <nav class="admin-nav">
    <?php foreach ($navSections as $sectionLabel => $items): ?>
      <?php if (!$items) continue; ?>
      <p class="admin-nav-label"><?= $e($sectionLabel) ?></p>
      <?php foreach ($items as $item): ?>
        <?php
        $isActive = $activeFile === $item['file'];
        if ($isActive && isset($item['type'])) {
            $isActive = $contentType === $item['type'];
        }
        ?>
        <a class="admin-nav-link<?= $isActive ? ' active' : '' ?>"
           href="<?= $e($item['href']) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
          <?= $icon($item['icon']) ?>
          <span><?= $e($item['label']) ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user">
      <span class="admin-user-avatar"><?= $e($initials !== '' ? $initials : 'A') ?></span>
      <span class="admin-user-info">
        <strong><?= $e($adminName) ?></strong>
        <small><?= $e($adminRole !== '' ? $adminRole : 'Admin') ?></small>
      </span>
    </div>
    <form method="post" action="logout.php" class="m-0">
      <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
      <button type="submit" class="admin-logout"><?= $icon('logout') ?> Logout</button>
    </form>
  </div>
</aside>
