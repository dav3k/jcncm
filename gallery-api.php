<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300');

$categories = ['vbs', 'worship', 'outreach', 'village'];
$result = [];

foreach ($categories as $cat) {
    $dir = __DIR__ . '/photos/' . $cat;
    $photos = [];
    if (is_dir($dir)) {
        foreach (scandir($dir) as $file) {
            if (preg_match('/\.(jpe?g|png|webp)$/i', $file)) {
                $photos[] = 'photos/' . $cat . '/' . $file;
            }
        }
        sort($photos);
    }
    if (count($photos) > 0) {
        $result[] = ['category' => $cat, 'photos' => $photos];
    }
}

echo json_encode($result);
