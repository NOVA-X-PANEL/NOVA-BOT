<?php
// Mirza Bot Long-Polling Worker for NOVA-X-PANEL
declare(strict_types=1);

chdir(__DIR__);
date_default_timezone_set('Asia/Tehran');
ini_set('memory_limit', '512M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/botapi.php';

global $APIKEY;

if (empty($APIKEY) || $APIKEY === '{API_KEY}') {
    fwrite(STDERR, "[Mirza Poller] APIKEY is not set in config.php. Exiting.\n");
    exit(1);
}

// Function to call telegram API with curl directly and SSL bypass
function safeTelegramCurl(string $url, int $timeout = 20): ?array {
    $ch = curl_init($url);
    if (!$ch) return null;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false || empty($raw)) {
        if (!empty($err)) {
            fwrite(STDERR, "[Mirza Poller cURL Error] {$err}\n");
        }
        return null;
    }
    return json_decode($raw, true);
}

// Clear webhook so getUpdates receives incoming messages without 409 Conflict
echo "[Mirza Poller] Clearing existing webhook...\n";
$delResp = safeTelegramCurl("https://api.telegram.org/bot{$APIKEY}/deleteWebhook?drop_pending_updates=false", 15);
echo "[Mirza Poller] deleteWebhook result: " . json_encode($delResp) . "\n";
echo "[Mirza Poller] Worker started. Listening for updates...\n";

$phpBin = defined('PHP_BINARY') && !empty(PHP_BINARY) ? PHP_BINARY : '/usr/bin/php';
$offset = 0;

while (true) {
    $url = "https://api.telegram.org/bot{$APIKEY}/getUpdates?offset={$offset}&timeout=25";
    $json = safeTelegramCurl($url, 35);

    if ($json === null) {
        sleep(2);
        continue;
    }

    if (!isset($json['ok']) || !$json['ok'] || !isset($json['result']) || !is_array($json['result'])) {
        $desc = (string)($json['description'] ?? 'unknown error');
        if (stripos($desc, 'Conflict') !== false || stripos($desc, 'webhook') !== false) {
            echo "[Mirza Poller] Webhook Conflict detected ({$desc}). Re-deleting webhook...\n";
            safeTelegramCurl("https://api.telegram.org/bot{$APIKEY}/deleteWebhook?drop_pending_updates=false", 15);
        }
        sleep(2);
        continue;
    }

    foreach ($json['result'] as $item) {
        $updateId = $item['update_id'];
        $offset = $updateId + 1;

        $sender = $item['message']['from']['id'] ?? $item['callback_query']['from']['id'] ?? 'unknown';
        $textMsg = $item['message']['text'] ?? $item['callback_query']['data'] ?? '';
        echo "[Mirza Poller] Processing update #{$updateId} from {$sender}: {$textMsg}\n";

        $payload = json_encode($item, JSON_UNESCAPED_UNICODE);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $proc = proc_open($phpBin . ' ' . escapeshellarg(__DIR__ . '/index.php'), $descriptors, $pipes, __DIR__);

        if (is_resource($proc)) {
            fwrite($pipes[0], $payload);
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($proc);

            if (!empty($err)) {
                fwrite(STDERR, "[Mirza Handler Error] " . substr($err, 0, 500) . "\n");
            }
        }
    }
}
