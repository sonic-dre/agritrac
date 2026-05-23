<?php
/**
 * Deployment webhook — called by GitHub Actions on every push to master.
 * Secured by a secret token stored in .env as DEPLOY_SECRET.
 * Access: https://agritrac.revelplatforms.com/deploy.php?secret=YOUR_SECRET
 */

// Load .env manually (framework not booted here)
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

$secret   = $_ENV['DEPLOY_SECRET'] ?? '';
$provided = $_GET['secret'] ?? '';

if (empty($secret) || !hash_equals($secret, $provided)) {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit("Forbidden\n");
}

$php  = '/opt/alt/php83/usr/bin/php';
$root = realpath(dirname(__DIR__));
$log  = ["=== Deploy started at " . date('Y-m-d H:i:s') . " ===\n"];

function run(string $cmd, string $cwd): array
{
    $proc = proc_open($cmd, [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes, $cwd);
    if (!is_resource($proc)) return ['out'=>'', 'err'=>'proc_open failed', 'code'=>1];
    $out  = stream_get_contents($pipes[1]);
    $err  = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return ['out' => trim($out), 'err' => trim($err), 'code' => proc_close($proc)];
}

$steps = [
    'git pull origin master',
    "$php $(which composer) install --no-dev --optimize-autoloader --no-interaction",
    "grep -q '^APP_KEY=base64:' .env || $php artisan key:generate --force",
    "$php artisan migrate --force",
    "$php artisan config:clear && $php artisan config:cache",
    "$php artisan route:clear && $php artisan route:cache",
    "$php artisan view:clear && $php artisan view:cache",
];

$failed = false;
foreach ($steps as $step) {
    $log[] = "\n$ $step";
    $r = run($step, $root);
    if ($r['out']) $log[] = $r['out'];
    if ($r['err']) $log[] = $r['err'];
    if ($r['code'] !== 0) {
        $log[] = "\n[FAILED — exit code {$r['code']}]";
        $failed = true;
        break;
    }
}

$log[] = $failed ? "\n=== Deployment FAILED ===" : "\n=== Deployment complete ===";

http_response_code($failed ? 500 : 200);
header('Content-Type: text/plain');
echo implode("\n", $log) . "\n";
