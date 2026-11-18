<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_role(['Super Admin', 'Content Manager']);
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM contact_messages WHERE id = :id')->execute([':id' => $id]);
            log_security_event($pdo, $_SESSION['admin_email'], "Deleted contact message #{$id}");
        }
        header('Location: contacts.php');
        exit;
    }
}

$rows = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 100')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Messages — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Contact Messages</h4>
      <a href="index.php" class="btn btn-sm btn-outline-dark">&larr; Dashboard</a>
    </div>
    <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Name</th><th>Contact</th><th>Subject</th><th>Message</th><th>Received</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= (int) $row['id'] ?></td>
              <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small">
                <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($row['phone'])): ?><br><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small" style="min-width: 240px; white-space: pre-wrap"><?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="small"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this contact message?')">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No contact messages yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
