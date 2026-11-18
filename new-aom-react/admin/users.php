<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_role(['Super Admin']);
header('Content-Type: text/html; charset=utf-8');

$roles = ['Super Admin', 'Content Manager', 'Moderator'];
$error = '';
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        if ($id === (int) $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $target = $stmt->fetch();
            if (!$target) {
                $error = 'The selected admin account no longer exists.';
            } else {
                if ($target['role'] === 'Super Admin' && (int) $target['is_active'] === 1) {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE role = 'Super Admin' AND is_active = 1");
                    if ((int) $stmt->fetchColumn() <= 1) {
                        $error = 'At least one active Super Admin must remain.';
                    }
                }
                if ($error === '') {
                    $pdo->prepare('DELETE FROM admin_users WHERE id = :id')->execute([':id' => $id]);
                    log_security_event($pdo, $_SESSION['admin_email'], "Deleted admin account #{$id}");
                    header('Location: users.php');
                    exit;
                }
            }
        }
    }

    if ($action === 'save') {
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $role = (string) ($_POST['role'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || strlen($name) > 120 || !$email || !in_array($role, $roles, true)) {
            $error = 'Enter a valid name, email address, and role.';
        } elseif ($id === 0 && strlen($password) < 12) {
            $error = 'New users need a password with at least 12 characters.';
        } elseif ($password !== '' && strlen($password) < 12) {
            $error = 'A new password must have at least 12 characters.';
        } else {
            $previous = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $id]);
                $previous = $stmt->fetch();
                if (!$previous) {
                    $error = 'The selected admin account no longer exists.';
                } elseif ($id === (int) $_SESSION['admin_id'] && $active === 0) {
                    $error = 'You cannot deactivate your own account.';
                }
            }

            if ($error === '' && $previous
                && $previous['role'] === 'Super Admin'
                && (int) $previous['is_active'] === 1
                && ($role !== 'Super Admin' || $active === 0)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE role = 'Super Admin' AND is_active = 1");
                if ((int) $stmt->fetchColumn() <= 1) {
                    $error = 'At least one active Super Admin must remain.';
                }
            }

            if ($error === '') {
                try {
                    if ($id > 0) {
                        $sql = 'UPDATE admin_users SET full_name = :name, email = :email, role = :role, is_active = :active';
                        $params = [':name' => $name, ':email' => $email, ':role' => $role, ':active' => $active, ':id' => $id];
                        if ($password !== '') {
                            $sql .= ', password_hash = :password';
                            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                        $sql .= ' WHERE id = :id';
                        $pdo->prepare($sql)->execute($params);
                        log_security_event($pdo, $_SESSION['admin_email'], "Updated admin account #{$id}");
                    } else {
                        $pdo->prepare(
                            'INSERT INTO admin_users (full_name, email, password_hash, role, is_active)
                             VALUES (:name, :email, :password, :role, :active)'
                        )->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                            ':role' => $role,
                            ':active' => $active,
                        ]);
                        log_security_event($pdo, $_SESSION['admin_email'], "Created admin account: {$email}");
                    }
                    header('Location: users.php');
                    exit;
                } catch (PDOException $databaseError) {
                    if (!in_array($databaseError->getCode(), ['23000', '23001'], true)) {
                        throw $databaseError;
                    }
                    $error = 'That email address already belongs to an admin account.';
                }
            }
        }
    }
}

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT id, full_name, email, role, is_active FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editId]);
    $editing = $stmt->fetch();
}
$users = $pdo->query('SELECT id, full_name, email, role, is_active, last_login, created_at FROM admin_users ORDER BY id')->fetchAll();
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Users & Roles — NEW AOM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div><h1 class="h4 fw-bold mb-1">Users & Roles</h1><p class="small text-muted mb-0">Only Super Admins can manage admin accounts.</p></div>
      <a href="index.php" class="btn btn-sm btn-outline-dark">Dashboard</a>
    </div>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>

    <section class="card border-0 shadow-sm rounded-4 p-4 mb-4">
      <h2 class="h5 fw-bold"><?= $editing ? 'Edit admin' : 'Add admin' ?></h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label" for="full-name">Full name</label><input id="full-name" name="full_name" class="form-control" maxlength="120" required value="<?= $e($editing['full_name'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" maxlength="150" required value="<?= $e($editing['email'] ?? '') ?>"></div>
          <div class="col-md-4">
            <label class="form-label" for="role">Role</label>
            <select id="role" name="role" class="form-select" required>
              <?php foreach ($roles as $role): ?><option value="<?= $e($role) ?>" <?= ($editing['role'] ?? 'Content Manager') === $role ? 'selected' : '' ?>><?= $e($role) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5"><label class="form-label" for="password"><?= $editing ? 'New password (optional)' : 'Password' ?></label><input id="password" name="password" type="password" class="form-control" minlength="12" <?= $editing ? '' : 'required' ?> autocomplete="new-password"><div class="form-text">At least 12 characters.</div></div>
          <div class="col-md-3 d-flex align-items-center"><div class="form-check mt-3"><input id="is-active" name="is_active" type="checkbox" class="form-check-input" value="1" <?= !isset($editing['is_active']) || (int) $editing['is_active'] === 1 ? 'checked' : '' ?>><label for="is-active" class="form-check-label">Active account</label></div></div>
          <div class="col-12 d-flex gap-2"><button class="btn btn-dark" type="submit">Save admin</button><?php if ($editing): ?><a href="users.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?></div>
        </div>
      </form>
    </section>

    <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= $e($user['full_name']) ?></td><td><?= $e($user['email']) ?></td><td><?= $e($user['role']) ?></td>
              <td><span class="badge <?= $user['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td class="small"><?= $e($user['last_login'] ?? 'Never') ?></td>
              <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-dark" href="?edit=<?= (int) $user['id'] ?>">Edit</a>
                <?php if ((int) $user['id'] !== (int) $_SESSION['admin_id']): ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this admin account?')">
                    <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
