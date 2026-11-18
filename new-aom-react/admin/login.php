<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: text/html; charset=utf-8');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submittedToken = (string) ($_POST['csrf_token'] ?? '');
  if (!hash_equals(csrf_token(), $submittedToken)) {
    http_response_code(403);
    exit('Invalid CSRF token.');
  }
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_email'] = $user['email'];

            $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = :id')
                ->execute([':id' => $user['id']]);
            log_security_event($pdo, $user['email'], 'Admin Login Success');

            header('Location: index.php');
            exit;
        } else {
            log_security_event($pdo, (string) ($_POST['email'] ?? ''), 'Admin Login Failed');
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NEW AOM — Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app admin-auth">
  <div class="container">
    <div class="admin-login-card mx-auto">
      <div class="brand-mark">N</div>
      <h4>NEW AOM Admin</h4>
      <p class="text-muted small mb-4">Sign in to manage website content and requests.</p>
      <?php if ($error): ?>
        <div class="alert alert-danger small"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label small">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Sign In</button>
      </form>
    </div>
  </div>
</body>
</html>