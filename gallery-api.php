<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300');

$photosDir = __DIR__ . '/photos';
$categories = [];

if (is_dir($photosDir)) {
    foreach (scandir($photosDir) as $entry) {
        if ($entry[0] !== '.' && is_dir($photosDir . '/' . $entry)) {
            $categories[] = $entry;
        }
    }
}

// Year-suffixed categories first (newest year first), then alphabetical
usort($categories, function($a, $b) {
    $ay = preg_match('/-(\d{4})$/', $a, $m) ? (int)$m[1] : 0;
    $by = preg_match('/-(\d{4})$/', $b, $m) ? (int)$m[1] : 0;
    if ($ay && $by) return $by - $ay;
    if ($ay) return -1;
    if ($by) return 1;
    return strcmp($a, $b);
});

$result = [];
foreach ($categories as $cat) {
    $dir = $photosDir . '/' . $cat;
    $photos = [];
    foreach (scandir($dir) as $file) {
        if (preg_match('/\.(jpe?g|png|webp)$/i', $file)) {
            $photos[] = 'photos/' . $cat . '/' . $file;
        }
    }
    sort($photos);
    if (count($photos) > 0) {
        $result[] = ['category' => $cat, 'photos' => $photos];
    }
}

echo json_encode($result);
