<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
header('Content-Type: text/html; charset=utf-8');

$modules = [
    'homepage' => ['Homepage Management', [
        'eyebrow' => ['Eyebrow', 'text'],
        'body' => ['Text', 'textarea'],
        'button_label' => ['Button label', 'text'],
        'button_url' => ['Button URL', 'text'],
        'imageUrl' => ['Hero / banner image', 'image'],
    ]],
    'about' => ['About Us', [
        'body' => ['About text', 'textarea'],
        'vision' => ['Vision', 'textarea'],
        'mission' => ['Mission', 'textarea'],
        'values' => ['Values', 'textarea'],
    ]],
    'services' => ['Services', [
        'category' => ['Category', 'text'],
        'shortDesc' => ['Short description', 'textarea'],
        'fullDesc' => ['Full description', 'textarea'],
        'icon' => ['Bootstrap icon class', 'text'],
        'duration' => ['Duration', 'text'],
        'mode' => ['Mode', 'text'],
        'imageUrl' => ['Service image (optional)', 'image'],
    ]],
    'knowledge' => ['Knowledge Centre', [
        'category' => ['Category', 'text'],
        'summary' => ['Summary', 'textarea'],
        'content' => ['Article content', 'textarea'],
        'date' => ['Publication date', 'date'],
        'imageUrl' => ['Article image', 'image'],
    ]],
    'spiritual-wisdom' => ['Spiritual Wisdom', [
        'category' => ['Category', 'text'],
        'summary' => ['Summary', 'textarea'],
        'content' => ['Content', 'textarea'],
    ]],
    'gallery' => ['Gallery', [
        'category' => ['Category', 'text'],
        'mediaType' => ['Media type (photo/video)', 'text'],
        'mediaUrl' => ['Media URL or upload (image / MP4 / WebM)', 'media'],
        'poster' => ['Video poster image (optional)', 'image'],
        'caption' => ['Caption', 'textarea'],
    ]],
    'ads' => ['Advertisements', [
        'body' => ['Description', 'textarea'],
        'imageUrl' => ['Banner image', 'image'],
        'linkUrl' => ['Destination URL', 'text'],
        'position' => ['Display position', 'text'],
        'startDate' => ['Start date', 'date'],
        'endDate' => ['End date', 'date'],
    ]],
    'events' => ['Workshops & Events', [
        'date' => ['Date', 'date'],
        'time' => ['Time', 'text'],
        'location' => ['Location', 'text'],
        'description' => ['Description', 'textarea'],
        'imageUrl' => ['Event image', 'image'],
        'bookingUrl' => ['Booking URL', 'text'],
    ]],
    'crowdfunding' => ['Crowdfunding', [
        'description' => ['Description', 'textarea'],
        'currency' => ['Currency', 'text'],
        'target' => ['Target amount', 'number'],
        'raised' => ['Raised amount', 'number'],
        'endDate' => ['End date', 'date'],
        'imageUrl' => ['Project image', 'image'],
    ]],
    'testimonials' => ['Testimonials', [
        'person' => ['Name or display name', 'text'],
        'quote' => ['Testimonial', 'textarea'],
        'consent' => ['Permission confirmed (yes/no)', 'text'],
        'photo' => ['Photo (optional)', 'image'],
        'rating' => ['Rating 1-5 (optional)', 'number'],
    ]],
    'faq' => ['FAQ', [
        'question' => ['Question', 'textarea'],
        'answer' => ['Answer', 'textarea'],
    ]],
    'settings' => ['Website Settings', [
        'siteTitle' => ['Website title', 'text'],
        'siteDescription' => ['Website description', 'textarea'],
        'logoUrl' => ['Logo URL or upload', 'image'],
        'contactEmail' => ['Contact email', 'email'],
        'phone' => ['Phone', 'text'],
        'whatsappNumber' => ['WhatsApp number (with country code)', 'text'],
        'address' => ['Address', 'textarea'],
        'mapEmbedUrl' => ['Map embed URL', 'url'],
        'socialLinks' => ['Social links (one URL per line)', 'textarea'],
        'analyticsId' => ['Google Analytics measurement ID', 'text'],
    ]],
    'translations' => ['Languages & Translations', [
        'translationKey' => ['Translation key', 'text'],
        'text' => ['Translated text', 'textarea'],
    ]],
];
$locales = ['en' => 'English', 'ta' => 'Tamil', 'fr' => 'French', 'zh' => 'Chinese (Simplified)', 'de-CH' => 'Swiss German'];
$adPositions = [
    'top-banner' => 'Top Banner',
    'hero' => 'Hero Area',
    'homepage-content' => 'Homepage Content',
    'services' => 'Services Section',
    'knowledge-centre' => 'Knowledge Centre',
    'events' => 'Workshops & Events',
    'sidebar' => 'Sidebar',
    'above-footer' => 'Above Footer',
    'top' => 'Top Banner (legacy)',
    'closing' => 'Additional Banner (legacy)',
];
$role = $_SESSION['admin_role'] ?? '';
$type = (string) ($_GET['type'] ?? 'homepage');
$locale = (string) ($_GET['locale'] ?? 'en');
$error = '';

if (!isset($modules[$type]) || !isset($locales[$locale])) {
    http_response_code(404);
    exit('Unknown content module or language.');
}
if ($role !== 'Super Admin' && $type === 'settings') {
    http_response_code(403);
    exit('Only a Super Admin can change website settings.');
}
if (!in_array($role, ['Super Admin', 'Content Manager', 'Moderator'], true)) {
    http_response_code(403);
    exit('You do not have permission to manage content.');
}
if ($role === 'Moderator' && !in_array($type, ['testimonials', 'faq'], true)) {
    http_response_code(403);
    exit('Moderators can only manage testimonials and FAQs.');
}

[$moduleTitle, $fields] = $modules[$type];
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM cms_content WHERE id = :id AND content_type = :type');
        $stmt->execute([':id' => $id, ':type' => $type]);
        log_security_event($pdo, $_SESSION['admin_email'], "Deleted {$type} content #{$id}");
        header('Location: content_manager.php?type=' . rawurlencode($type) . '&locale=' . rawurlencode($locale));
        exit;
    }

    if ($action === 'save') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $status = (string) ($_POST['status'] ?? 'draft');
        $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
        $payload = [];

        foreach ($fields as $key => [$label, $inputType]) {
            $value = trim((string) ($_POST['data'][$key] ?? ''));
            $fileError = $_FILES['upload']['error'][$key] ?? UPLOAD_ERR_NO_FILE;
            if ($fileError === UPLOAD_ERR_OK) {
                $tmp = (string) ($_FILES['upload']['tmp_name'][$key] ?? '');
                $size = (int) ($_FILES['upload']['size'][$key] ?? 0);
                $mime = is_uploaded_file($tmp) && function_exists('finfo_open')
                    ? (string) finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp)
                    : '';
                $allowedMime = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                ];
                if ($inputType === 'media') {
                    $allowedMime['video/mp4'] = 'mp4';
                    $allowedMime['video/webm'] = 'webm';
                }
                if ($size === 0 || $size > 8 * 1024 * 1024) {
                    $error = "{$label} must be smaller than 8 MB.";
                    break;
                }
                if (!isset($allowedMime[$mime])) {
                    $error = $inputType === 'media'
                        ? "{$label}: upload a JPEG, PNG, WEBP, GIF, MP4, or WebM file."
                        : "{$label}: upload a JPEG, PNG, WEBP, or GIF image.";
                    break;
                }
                $uploadDir = __DIR__ . '/../uploads/cms';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    $error = 'The uploads folder could not be created.';
                    break;
                }
                $fileName = $type . '-' . $key . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
                if (!move_uploaded_file($tmp, $uploadDir . '/' . $fileName)) {
                    $error = "{$label} could not be saved. Check folder permissions.";
                    break;
                }
                $value = 'uploads/cms/' . $fileName;
            }
            if ($type === 'gallery' && $key === 'mediaType' && !in_array($value, ['photo', 'video'], true)) {
                $error = 'Choose photo or video for the gallery media type.';
                break;
            }
            if ($type === 'testimonials' && $key === 'consent'
                && !in_array(strtolower($value), ['yes', 'no'], true)) {
                $error = 'Confirm whether the contributor gave permission by choosing yes or no.';
                break;
            }
            if ($key === 'socialLinks' && $value !== '') {
                foreach (preg_split('/\r\n|\r|\n/', $value) as $socialUrl) {
                    if ($socialUrl !== '' && !preg_match('~^https://[^\s]+$~i', $socialUrl)) {
                        $error = 'Social links must be HTTPS URLs, one per line.';
                        break 2;
                    }
                }
            }
            if (preg_match('/(?:url|_url)$/i', $key) && $value !== ''
                && !preg_match('~^(https?://|/)[^\s]*$~i', $value)
                && !(in_array($key, ['mediaUrl', 'imageUrl', 'logoUrl', 'photo'], true)
                    && preg_match('~^uploads/cms/[A-Za-z0-9._-]+$~', $value))) {
                $error = "{$label} must be an HTTP(S) URL or a site-relative path.";
                break;
            }
            if ($inputType === 'number' && $value !== '') {
                if (!is_numeric($value)) {
                    $error = "{$label} must be a number.";
                    break;
                }
                $payload[$key] = (float) $value;
            } else {
                $payload[$key] = $value;
            }
        }

        if ($error === '' && ($title === '' || strlen($title) > 240)) {
            $error = 'Enter a title of 1–240 characters.';
        }
        if ($error === '' && (strlen($slug) > 160 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug))) {
            $error = 'Use a lowercase URL slug containing letters, numbers, and hyphens.';
        }
        if ($error === '' && (!in_array($status, ['draft', 'published'], true) || $sortOrder === false)) {
            $error = 'Choose a valid publication status and display order.';
        }
        if ($error === '' && $role === 'Moderator' && $type !== 'testimonials' && $type !== 'faq') {
            http_response_code(403);
            exit('Moderators can only manage testimonials and FAQs.');
        }

        if ($error === '') {
            try {
                $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (JsonException $jsonError) {
                $error = 'Content contains invalid text encoding and could not be saved.';
            }

            if ($error === '') {
                try {
                    if ($id > 0) {
                        $stmt = $pdo->prepare(
                            'UPDATE cms_content
                             SET slug = :slug, locale = :locale, title = :title, payload_json = :payload,
                                 status = :status, sort_order = :sort_order, updated_by = :admin_id
                             WHERE id = :id AND content_type = :type'
                        );
                        $stmt->execute([
                            ':slug' => $slug, ':locale' => $locale, ':title' => $title, ':payload' => $json,
                            ':status' => $status, ':sort_order' => $sortOrder, ':admin_id' => $_SESSION['admin_id'],
                            ':id' => $id, ':type' => $type,
                        ]);
                    } else {
                        $stmt = $pdo->prepare(
                            'INSERT INTO cms_content
                             (content_type, slug, locale, title, payload_json, status, sort_order, updated_by)
                             VALUES (:type, :slug, :locale, :title, :payload, :status, :sort_order, :admin_id)'
                        );
                        $stmt->execute([
                            ':type' => $type, ':slug' => $slug, ':locale' => $locale, ':title' => $title,
                            ':payload' => $json, ':status' => $status, ':sort_order' => $sortOrder,
                            ':admin_id' => $_SESSION['admin_id'],
                        ]);
                    }
                    log_security_event($pdo, $_SESSION['admin_email'], "Saved {$type} content: {$slug} ({$locale})");
                    header('Location: content_manager.php?type=' . rawurlencode($type) . '&locale=' . rawurlencode($locale));
                    exit;
                } catch (PDOException $databaseError) {
                    if (!in_array($databaseError->getCode(), ['23000', '23001'], true)) {
                        throw $databaseError;
                    }
                    $error = 'That slug is already in use for this content module and language.';
                }
            }
        }
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM cms_content WHERE id = :id AND content_type = :type LIMIT 1');
    $stmt->execute([':id' => $editId, ':type' => $type]);
    $editing = $stmt->fetch();
    if ($editing) {
        $locale = $editing['locale'];
    }
}

$stmt = $pdo->prepare(
    'SELECT * FROM cms_content WHERE content_type = :type AND locale = :locale ORDER BY sort_order, id DESC'
);
$stmt->execute([':type' => $type, ':locale' => $locale]);
$rows = $stmt->fetchAll();
$currentData = $editing ? json_decode($editing['payload_json'], true) : [];
if ($type === 'gallery' && is_array($currentData) && !isset($currentData['mediaType'])) {
    $currentData['mediaType'] = $currentData['type'] ?? '';
}
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($moduleTitle) ?> — NEW AOM Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/admin-ui.css" rel="stylesheet">
</head>
<body class="admin-app">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="h4 fw-bold mb-1"><?= $e($moduleTitle) ?></h1>
        <p class="small text-muted mb-0">Manage localized content and publishing status.</p>
      </div>
      <a href="index.php" class="btn btn-sm btn-outline-dark">Dashboard</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php foreach ($modules as $key => [$label]): ?>
        <?php if ($role !== 'Super Admin' && $key === 'settings') continue; ?>
        <?php if ($role === 'Moderator' && !in_array($key, ['testimonials', 'faq'], true)) continue; ?>
        <a class="btn btn-sm <?= $key === $type ? 'btn-dark' : 'btn-outline-dark' ?>"
           href="?type=<?= rawurlencode($key) ?>&locale=<?= rawurlencode($locale) ?>"><?= $e($label) ?></a>
      <?php endforeach; ?>
    </div>

    <form method="get" class="row g-2 align-items-end mb-4">
      <input type="hidden" name="type" value="<?= $e($type) ?>">
      <div class="col-auto">
        <label for="locale" class="form-label small">Language</label>
        <select id="locale" name="locale" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($locales as $code => $label): ?>
            <option value="<?= $e($code) ?>" <?= $locale === $code ? 'selected' : '' ?>><?= $e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>

    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
      <h2 class="h5 fw-bold"><?= $editing ? 'Edit content' : 'Add content' ?></h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="content-title">Title</label>
            <input id="content-title" name="title" class="form-control" maxlength="240" required value="<?= $e($editing['title'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="content-slug">URL slug</label>
            <input id="content-slug" name="slug" class="form-control" maxlength="160" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required value="<?= $e($editing['slug'] ?? '') ?>">
          </div>
          <?php foreach ($fields as $key => [$label, $inputType]): ?>
            <div class="col-md-6">
              <label class="form-label" for="field-<?= $e($key) ?>"><?= $e($label) ?></label>
              <?php if ($key === 'mediaType'): ?>
                <select id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" class="form-select">
                  <option value="photo" <?= ($currentData[$key] ?? 'photo') === 'photo' ? 'selected' : '' ?>>Photo</option>
                  <option value="video" <?= ($currentData[$key] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                </select>
              <?php elseif ($key === 'consent'): ?>
                <select id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" class="form-select">
                  <option value="no" <?= strtolower((string) ($currentData[$key] ?? 'no')) === 'no' ? 'selected' : '' ?>>No</option>
                  <option value="yes" <?= strtolower((string) ($currentData[$key] ?? '')) === 'yes' ? 'selected' : '' ?>>Yes</option>
                </select>
              <?php elseif ($key === 'position'): ?>
                <select id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" class="form-select">
                  <?php foreach ($adPositions as $posValue => $posLabel): ?>
                    <option value="<?= $e($posValue) ?>" <?= ($currentData[$key] ?? '') === $posValue ? 'selected' : '' ?>><?= $e($posLabel) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($inputType === 'image' || $inputType === 'media'): ?>
                <input id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" type="text" class="form-control" value="<?= $e($currentData[$key] ?? '') ?>" placeholder="https://... or uploads/cms/...">
                <input type="file" name="upload[<?= $e($key) ?>]" class="form-control form-control-sm mt-2" accept="<?= $inputType === 'media' ? 'image/*,video/mp4,video/webm' : 'image/*' ?>">
                <div class="form-text">Enter a URL or upload a file (max 8 MB).</div>
              <?php elseif ($inputType === 'textarea'): ?>
                <textarea id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" class="form-control" rows="3"><?= $e($currentData[$key] ?? '') ?></textarea>
              <?php else: ?>
                <input id="field-<?= $e($key) ?>" name="data[<?= $e($key) ?>]" type="<?= $e($inputType) ?>" class="form-control" value="<?= $e($currentData[$key] ?? '') ?>">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div class="col-md-4">
            <label class="form-label" for="content-status">Status</label>
            <select id="content-status" name="status" class="form-select">
              <option value="draft" <?= ($editing['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= ($editing['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="content-order">Display order</label>
            <input id="content-order" name="sort_order" type="number" class="form-control" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-dark">Save content</button>
            <?php if ($editing): ?><a href="?type=<?= rawurlencode($type) ?>&locale=<?= rawurlencode($locale) ?>" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
          </div>
          <?php if ($type === 'ads' && !empty($currentData['imageUrl'])): ?>
            <div class="col-12">
              <p class="form-label small mb-1">Banner preview</p>
              <img class="img-fluid rounded-3 border" style="max-height: 180px" src="<?= $e(str_starts_with((string) $currentData['imageUrl'], 'http') ? $currentData['imageUrl'] : '../' . $currentData['imageUrl']) ?>" alt="Advertisement preview">
            </div>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= $e($row['title']) ?></td>
              <td><?= $e($row['slug']) ?></td>
              <td><span class="badge <?= $row['status'] === 'published' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $e($row['status']) ?></span></td>
              <td class="small"><?= $e($row['updated_at']) ?></td>
              <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-dark" href="?type=<?= rawurlencode($type) ?>&locale=<?= rawurlencode($locale) ?>&edit=<?= (int) $row['id'] ?>">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this content item?')">
                  <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">No content in this language yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
