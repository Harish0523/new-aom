<?php
require_once __DIR__ . '/includes/auth.php';
if (!function_exists('require_admin_login')) {
    function require_admin_login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_email'])) {
            header('Location: login.php');
            exit;
        }
    }
}
require_admin_role(['Super Admin', 'Content Manager']);
header('Content-Type: text/html; charset=utf-8');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin_role(['Super Admin']);
    verify_csrf();
    $bankName = trim($_POST['bank_name'] ?? '');
    $holder   = trim($_POST['account_holder'] ?? '');
    $accNo    = trim($_POST['account_number'] ?? '');
    $branch   = trim($_POST['branch_name'] ?? '');
    $swift    = trim($_POST['swift_code'] ?? '');

    $current = $pdo->query('SELECT * FROM donation_bank_settings ORDER BY id DESC LIMIT 1')->fetch();
    $qrPath = $current['qr_image_path'] ?? 'uploads/donations/default-qr.png';

    if (!empty($_FILES['qr_image']['name']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
        $mime = function_exists('finfo_open')
            ? (string) finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['qr_image']['tmp_name'])
            : '';
        $allowedMime = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)
            && ($mime === '' || isset($allowedMime[$mime]))) {
            $dir = __DIR__ . '/../uploads/donations';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $fileName = 'qr_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $dir . '/' . $fileName)) {
                $qrPath = 'uploads/donations/' . $fileName;
            }
        }
    }

    $params = [
        ':b'  => htmlspecialchars($bankName, ENT_QUOTES, 'UTF-8'),
        ':h'  => htmlspecialchars($holder, ENT_QUOTES, 'UTF-8'),
        ':a'  => htmlspecialchars($accNo, ENT_QUOTES, 'UTF-8'),
        ':br' => htmlspecialchars($branch, ENT_QUOTES, 'UTF-8'),
        ':s'  => htmlspecialchars($swift, ENT_QUOTES, 'UTF-8'),
        ':q'  => $qrPath,
    ];
    if ($current) {
        $params[':id'] = (int) $current['id'];
        $stmt = $pdo->prepare(
            'UPDATE donation_bank_settings
             SET bank_name = :b, account_holder = :h, account_number = :a,
                 branch_name = :br, swift_code = :s, qr_image_path = :q
             WHERE id = :id'
        );
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO donation_bank_settings (bank_name, account_holder, account_number, branch_name, swift_code, qr_image_path)
             VALUES (:b, :h, :a, :br, :s, :q)'
        );
    }
    $stmt->execute($params);
    log_security_event($pdo, $_SESSION['admin_email'], 'Updated Donation Bank & QR Settings');
    $msg = 'Bank details and QR code updated.';
}

$bank = $pdo->query('SELECT * FROM donation_bank_settings ORDER BY id DESC LIMIT 1')->fetch();
$otps = $pdo->query('SELECT * FROM donation_otps ORDER BY created_at DESC LIMIT 30')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donations & Bank Details — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Donations — Bank Details & QR Code</h4>
      <a href="index.php" class="btn btn-sm btn-outline-dark">&larr; Dashboard</a>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-success small"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($bank['bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Account Holder</label>
            <input type="text" name="account_holder" class="form-control" value="<?= htmlspecialchars($bank['account_holder'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Account Number</label>
            <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($bank['account_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Branch Name</label>
            <input type="text" name="branch_name" class="form-control" value="<?= htmlspecialchars($bank['branch_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">SWIFT Code (Optional)</label>
            <input type="text" name="swift_code" class="form-control" value="<?= htmlspecialchars($bank['swift_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small">Upload QR Code Image (PNG/JPG/WEBP)</label>
            <input type="file" name="qr_image" accept="image/png,image/jpeg,image/webp" class="form-control">
            <?php if (!empty($bank['qr_image_path'])): ?>
              <div class="form-text">Current: <?= htmlspecialchars($bank['qr_image_path'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-dark">Save Bank & QR Details</button>
          </div>
        </div>
      </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
      <h6 class="fw-bold mb-3">Recent Donation OTP Requests</h6>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Name</th><th>Email</th><th>Project</th><th>Currency</th><th>Verified</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($otps as $o): ?>
              <tr>
                <td><?= htmlspecialchars($o['donor_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($o['donor_email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($o['project_title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($o['currency'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $o['is_verified'] ? 'Yes' : 'No' ?></td>
                <td class="small"><?= htmlspecialchars($o['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>