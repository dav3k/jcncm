<?php
// GitHub webhook → auto deploy
// Place this file at: public_html/deploy.php
// Configure in GitHub: Settings → Webhooks → Add:
//   Payload URL: https://jcncm.org/deploy.php
//   Content type: application/json
//   Secret: (set WEBHOOK_SECRET below to the same value)
//   Events: Just the push event

// ─── CONFIG ──────────────────────────────────────────────────────────────
// Change this to a strong random string. Must match GitHub webhook secret.
// Secret is set on the server only (not in git).
// Edit this value directly in cPanel File Manager → public_html/deploy.php
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: 'SET_ON_SERVER');

// Absolute path to the cPanel-managed git repo.
// Typically: /home/<cpanel_user>/repositories/jcncm
define('REPO_PATH', '/home/jcncmc8d/repositories/jcncm');

// Absolute path to public_html (where the site is served from).
define('DEPLOY_PATH', '/home/jcncmc8d/public_html');

// Path to git binary on BigRock cPanel.
define('GIT_BIN', '/usr/local/cpanel/3rdparty/bin/git');

// ─── VERIFY ──────────────────────────────────────────────────────────────
header('Content-Type: text/plain');

$raw = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!$sigHeader) {
    http_response_code(400);
    exit('Missing signature header');
}
$expected = 'sha256=' . hash_hmac('sha256', $raw, WEBHOOK_SECRET);
if (!hash_equals($expected, $sigHeader)) {
    http_response_code(403);
    exit('Invalid signature');
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event === 'ping') { echo "pong\n"; exit; }
if ($event !== 'push') { echo "Ignored event: $event\n"; exit; }

// ─── DEPLOY ──────────────────────────────────────────────────────────────
$log = [];
$log[] = '[' . date('c') . '] Deploy triggered';

function run($cmd, &$log) {
    $log[] = '$ ' . $cmd;
    exec($cmd . ' 2>&1', $out, $code);
    foreach ($out as $line) $log[] = '  ' . $line;
    return $code === 0;
}

// Pull latest from origin
$ok  = run('cd ' . escapeshellarg(REPO_PATH) . ' && ' . GIT_BIN . ' fetch origin main', $log);
$ok &= run('cd ' . escapeshellarg(REPO_PATH) . ' && ' . GIT_BIN . ' reset --hard origin/main', $log);

if (!$ok) { http_response_code(500); echo implode("\n", $log); exit; }

// Copy files from repo → public_html
$files = ['index.html', 'logo.jpg', 'robots.txt', 'sitemap.xml'];
foreach ($files as $f) {
    run('cp ' . escapeshellarg(REPO_PATH . '/' . $f) . ' ' . escapeshellarg(DEPLOY_PATH . '/'), $log);
}
run('cp -r ' . escapeshellarg(REPO_PATH . '/photos') . ' ' . escapeshellarg(DEPLOY_PATH . '/'), $log);
run('cp -r ' . escapeshellarg(REPO_PATH . '/video') . ' ' . escapeshellarg(DEPLOY_PATH . '/'), $log);

$log[] = '[' . date('c') . '] Done';
echo implode("\n", $log);
