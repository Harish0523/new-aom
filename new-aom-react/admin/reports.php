<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_role(['Super Admin']);
header('Content-Type: text/html; charset=utf-8');

$reportTypes = [
    'bookings' => 'Bookings',
    'contacts' => 'Contact messages',
    'donations' => 'Donation OTP activity',
    'visitors' => 'Website visitors',
];
$reportType = (string) ($_GET['report'] ?? 'bookings');
$from = (string) ($_GET['from'] ?? date('Y-m-01'));
$to = (string) ($_GET['to'] ?? date('Y-m-d'));
$isDate = static function (string $date): bool {
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
};

if (!isset($reportTypes[$reportType]) || !$isDate($from) || !$isDate($to) || $from > $to) {
    http_response_code(400);
    exit('Select a supported report and a valid date range.');
}

$queries = [
    'bookings' => 'SELECT id, full_name AS name, email, phone, service_name AS service, preferred_date, preferred_time, status, created_at
                   FROM bookings WHERE created_at >= :from AND created_at < DATE_ADD(:to, INTERVAL 1 DAY)
                   ORDER BY created_at DESC LIMIT 5000',
    'contacts' => 'SELECT id, name, email, phone, subject, message, created_at
                   FROM contact_messages WHERE created_at >= :from AND created_at < DATE_ADD(:to, INTERVAL 1 DAY)
                   ORDER BY created_at DESC LIMIT 5000',
    'donations' => 'SELECT id, donor_name, donor_email, project_title, currency, is_verified, created_at
                    FROM donation_otps WHERE created_at >= :from AND created_at < DATE_ADD(:to, INTERVAL 1 DAY)
                    ORDER BY created_at DESC LIMIT 5000',
    'visitors' => 'SELECT page, COUNT(*) AS hits, COUNT(DISTINCT ip_hash) AS unique_visitors, MAX(visited_at) AS last_visit
                   FROM visitors WHERE visited_at >= :from AND visited_at < DATE_ADD(:to, INTERVAL 1 DAY)
                   GROUP BY page ORDER BY hits DESC LIMIT 5000',
];
try {
    $stmt = $pdo->prepare($queries[$reportType]);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $rows = $stmt->fetchAll();
} catch (PDOException $ex) {
    $rows = [];
    $reportError = $ex->getCode() === '42S02'
        ? 'Visitor tracking has no data yet. Load the public website once, then run the report again.'
        : 'The requested report could not be loaded.';
}

$visitorTotals = ['today' => 0, 'week' => 0, 'all' => 0];
try {
    $visitorTotals['today'] = (int) $pdo->query('SELECT COUNT(*) FROM visitors WHERE visited_at >= CURDATE()')->fetchColumn();
    $visitorTotals['week'] = (int) $pdo->query('SELECT COUNT(*) FROM visitors WHERE visited_at >= (CURDATE() - INTERVAL 6 DAY)')->fetchColumn();
    $visitorTotals['all'] = (int) $pdo->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
} catch (PDOException $ex) {
    // visitors table is created lazily on the first public page view
}

if (($_GET['download'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="new-aom-' . $reportType . '-' . $from . '-to-' . $to . '.csv"');
    $output = fopen('php://output', 'w');
    if ($output === false) {
        http_response_code(500);
        exit('Could not create report download.');
    }
    fwrite($output, "\xEF\xBB\xBF");
    if ($rows) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            $values = array_map(static function ($value) {
                $text = (string) ($value ?? '');
                return preg_match('/^[=+\-@\t\r]/', $text) ? "'" . $text : $text;
            }, array_values($row));
            fputcsv($output, $values);
        }
    }
    fclose($output);
    exit;
}

$contentCounts = $pdo->query(
    'SELECT content_type, status, COUNT(*) AS total FROM cms_content GROUP BY content_type, status ORDER BY content_type, status'
)->fetchAll();
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div><h1 class="h4 fw-bold mb-1">Reports</h1><p class="small text-muted mb-0">Export operational records and review CMS publishing counts.</p></div>
      <a href="index.php" class="btn btn-sm btn-outline-dark">Dashboard</a>
    </div>
    <form method="get" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4"><label class="form-label" for="report">Report</label><select id="report" name="report" class="form-select"><?php foreach ($reportTypes as $key => $label): ?><option value="<?= $e($key) ?>" <?= $reportType === $key ? 'selected' : '' ?>><?= $e($label) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label" for="from">From</label><input id="from" name="from" type="date" class="form-control" value="<?= $e($from) ?>" required></div>
        <div class="col-md-3"><label class="form-label" for="to">To</label><input id="to" name="to" type="date" class="form-control" value="<?= $e($to) ?>" required></div>
        <div class="col-md-2 d-grid"><button class="btn btn-dark">Run report</button></div>
      </div>
      <div class="mt-3"><a class="btn btn-sm btn-outline-success" href="?report=<?= rawurlencode($reportType) ?>&from=<?= rawurlencode($from) ?>&to=<?= rawurlencode($to) ?>&download=csv">Download CSV</a><span class="small text-muted ms-2">Maximum 5,000 rows per export.</span></div>
    </form>

    <?php if (!empty($reportError)): ?><div class="alert alert-warning small"><?= $e($reportError) ?></div><?php endif; ?>

    <div class="row g-3 mb-4">
      <?php foreach (['today' => 'Visitors today', 'week' => 'Last 7 days', 'all' => 'All time visits'] as $key => $label): ?>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 p-3"><div class="small text-muted"><?= $e($label) ?></div><div class="h3 fw-bold mb-0"><?= (int) $visitorTotals[$key] ?></div></div></div>
      <?php endforeach; ?>
    </div>

    <section class="card border-0 shadow-sm rounded-4 p-3 mb-4">
      <h2 class="h6 fw-bold"><?= $e($reportTypes[$reportType]) ?> <span class="text-muted fw-normal">(<?= count($rows) ?> rows)</span></h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead><tr><?php foreach (array_keys($rows[0] ?? ['No data' => ''] ) as $column): ?><th><?= $e(ucwords(str_replace('_', ' ', $column))) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $value): ?><td class="small"><?= $e($value) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
            <?php if (!$rows): ?><tr><td class="text-center text-muted py-4">No matching records for this date range.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card border-0 shadow-sm rounded-4 p-3">
      <h2 class="h6 fw-bold">CMS content status</h2>
      <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Module</th><th>Status</th><th>Items</th></tr></thead><tbody>
        <?php foreach ($contentCounts as $item): ?><tr><td><?= $e($item['content_type']) ?></td><td><?= $e($item['status']) ?></td><td><?= (int) $item['total'] ?></td></tr><?php endforeach; ?>
        <?php if (!$contentCounts): ?><tr><td colspan="3" class="text-center text-muted">CMS has no saved content yet.</td></tr><?php endif; ?>
      </tbody></table></div>
    </section>
  </main>
</body>
</html>
