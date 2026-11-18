<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0 && in_array($action, ['Approved', 'Rejected', 'Pending'], true)) {
        $stmt = $pdo->prepare('UPDATE bookings SET status = :st WHERE id = :id');
        $stmt->execute([':st' => $action, ':id' => $id]);
        log_security_event($pdo, $_SESSION['admin_email'], "Booking #{$id} set to {$action}");
    } elseif ($id > 0 && $action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
        $stmt->execute([':id' => $id]);
        log_security_event($pdo, $_SESSION['admin_email'], "Booking #{$id} deleted");
    }
    header('Location: bookings.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM bookings ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Bookings — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <div class="admin-shell">
    <div class="admin-page-header">
      <div>
        <h4>Online Bookings</h4>
      </div>
      <a href="index.php" class="btn btn-sm btn-outline-dark">&larr; Dashboard</a>
    </div>
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Contact</th><th>Service</th><th>Date & Time</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int) $r['id'] ?></td>
              <td><?= htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small"><?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars($r['phone'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($r['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small"><?= htmlspecialchars($r['preferred_date'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($r['preferred_time'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <button name="action" value="Approved" class="btn btn-sm btn-success">Approve</button>
                  <button name="action" value="Rejected" class="btn btn-sm btn-warning">Reject</button>
                  <button name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete booking?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
