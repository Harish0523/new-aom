<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_role(['Super Admin']);
header('Content-Type: text/html; charset=utf-8');

$ready = false;
try {
    $pdo->query('SELECT 1 FROM cms_content LIMIT 1');
    $ready = true;
} catch (PDOException $error) {
    if ($error->getCode() !== '42S02') {
        throw $error;
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $schemaPath = __DIR__ . '/../Backend/database/cms_schema.sql';
    $seedPath = __DIR__ . '/../Backend/database/cms_seed.sql';
    $schemaSql = file_get_contents($schemaPath);
    $seedSql = file_get_contents($seedPath);
    if ($schemaSql === false || $seedSql === false) {
        http_response_code(500);
        exit('CMS migration files could not be read.');
    }

    $schemaSql = preg_replace('/^\s*USE\s+new_aom_db\s*;\s*/i', '', $schemaSql, 1);
    $seedSql = preg_replace('/^\s*USE\s+new_aom_db\s*;\s*/i', '', $seedSql, 1);
    if ($schemaSql === null || $seedSql === null || trim($schemaSql) === '' || trim($seedSql) === '') {
        http_response_code(500);
        exit('CMS migration files are invalid.');
    }

    $pdo->exec($schemaSql);
    $pdo->exec($seedSql);
    log_security_event($pdo, $_SESSION['admin_email'], 'Installed or updated CMS schema and starter content');
    $ready = true;
    $message = 'CMS database is ready. Starter homepage, service, article, event, gallery, donation, and FAQ content has been added where missing.';
}

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CMS Setup — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="container py-5" style="max-width: 760px">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
      <h1 class="h4 fw-bold">CMS database setup</h1>
      <?php if ($message !== ''): ?><div class="alert alert-success"><?= $e($message) ?></div><?php endif; ?>
      <p class="text-muted">This installs the CMS content table and inserts the starter content without replacing existing records.</p>
      <p>Status: <span class="badge <?= $ready ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $ready ? 'Installed' : 'Not installed' ?></span></p>
      <div class="d-flex flex-wrap gap-2">
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
          <button type="submit" class="btn btn-dark"><?= $ready ? 'Apply missing starter content' : 'Install CMS database' ?></button>
        </form>
        <a href="index.php" class="btn btn-outline-dark">Return to dashboard</a>
      </div>
    </div>
  </main>
</body>
</html>
