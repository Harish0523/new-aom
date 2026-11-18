<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$allowedTypes = [
    'homepage', 'about', 'services', 'knowledge', 'spiritual-wisdom',
    'gallery', 'ads', 'events', 'crowdfunding', 'testimonials', 'faq',
    'settings', 'translations',
];
$allowedLocales = ['en', 'ta', 'fr', 'zh', 'de-CH'];
$type = trim((string) ($_GET['type'] ?? ''));
$locale = trim((string) ($_GET['locale'] ?? 'en'));

if (($type !== 'all' && !in_array($type, $allowedTypes, true)) || !in_array($locale, $allowedLocales, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A supported content type and language are required.']);
    exit;
}

$sql = 'SELECT id, content_type, slug, locale, title, payload_json, sort_order, updated_at
        FROM cms_content
        WHERE locale IN (:locale, "en") AND status = "published"';
$params = [':locale' => $locale];
if ($type !== 'all') {
    $sql .= ' AND content_type = :type';
    $params[':type'] = $type;
}
$sql .= ' ORDER BY content_type ASC, slug ASC, CASE WHEN locale = "en" THEN 0 ELSE 1 END ASC, sort_order ASC, id ASC';
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $error) {
    if ($error->getCode() !== '42S02') {
        throw $error;
    }
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'CMS database is not installed. Sign in as Super Admin and run the CMS setup page.']);
    exit;
}

$items = [];
foreach ($rows as $row) {
    $data = json_decode($row['payload_json'], true);
    if (!is_array($data)) {
        continue;
    }

    $slug = $row['slug'];
    $collection = $row['content_type'];
    if (!isset($items[$collection])) {
        $items[$collection] = [];
    }
    if (!isset($items[$collection][$slug])) {
        $items[$collection][$slug] = [
            'id' => (int) $row['id'],
            'type' => $row['content_type'],
            'slug' => $slug,
            'locale' => $row['locale'],
            'title' => $row['title'],
            'data' => $data,
            'sortOrder' => (int) $row['sort_order'],
            'updatedAt' => $row['updated_at'],
        ];
        continue;
    }

    if ($row['locale'] === $locale && $locale !== 'en') {
        $items[$collection][$slug]['id'] = (int) $row['id'];
        $items[$collection][$slug]['locale'] = $locale;
        $items[$collection][$slug]['title'] = $row['title'];
        $items[$collection][$slug]['data'] = array_replace($items[$collection][$slug]['data'], $data);
        $items[$collection][$slug]['updatedAt'] = $row['updated_at'];
    }
}

$formattedItems = [];
foreach ($items as $collection => $collectionItems) {
    usort($collectionItems, static function (array $left, array $right): int {
        return ($left['sortOrder'] <=> $right['sortOrder']) ?: ($left['id'] <=> $right['id']);
    });
    $formattedItems[$collection] = array_values($collectionItems);
}

// Advertisements respect their start/end dates so expired banners stop showing automatically.
$today = date('Y-m-d');
$adIsLive = static function (array $ad) use ($today): bool {
    $start = trim((string) ($ad['data']['startDate'] ?? ''));
    $end = trim((string) ($ad['data']['endDate'] ?? ''));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && $start > $today) {
        return false;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) && $end < $today) {
        return false;
    }
    return true;
};
if (isset($formattedItems['ads'])) {
    $formattedItems['ads'] = array_values(array_filter($formattedItems['ads'], $adIsLive));
}
echo json_encode([
    'status' => 'ok',
    'items' => $type === 'all' ? $formattedItems : ($formattedItems[$type] ?? []),
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
