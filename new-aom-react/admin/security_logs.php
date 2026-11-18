<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_role(['Super Admin']);
header('Content-Type: text/html; charset=utf-8');

$rows = $pdo->query('SELECT * FROM security_logs ORDER BY created_at DESC LIMIT 100')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Security Logs — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Security Logs</h4>
      <a href="index.php" class="btn btn-sm btn-outline-dark">&larr; Dashboard</a>
    </div>
    <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Admin Email</th><th>Action</th><th>IP Address</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= (int) $row['id'] ?></td>
              <td><?= htmlspecialchars($row['user_email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['ip_address'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No security events yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
