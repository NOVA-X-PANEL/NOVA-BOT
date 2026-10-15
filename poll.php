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

// Clear webhook so getUpdates receives incoming messages without 409 Conflict
echo "[Mirza Poller] Clearing existing webhook...\n";
$ch = curl_init("https://api.telegram.org/bot{$APIKEY}/deleteWebhook?drop_pending_updates=false");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$delResp = curl_exec($ch);
curl_close($ch);
echo "[Mirza Poller] deleteWebhook result: " . ($delResp ?: 'ok') . "\n";
echo "[Mirza Poller] Worker started. Listening for updates...\n";

$offset = 0;
while (true) {
    $url = "https://api.telegram.org/bot{$APIKEY}/getUpdates?offset={$offset}&timeout=25";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 35);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    $raw = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($raw === false || empty($raw)) {
        if (!empty($curlErr)) {
            fwrite(STDERR, "[Mirza Poller] cURL error: {$curlErr}\n");
        }
        sleep(2);
        continue;
    }

    $json = json_decode($raw, true);
    if (!isset($json['ok']) || !$json['ok'] || !isset($json['result']) || !is_array($json['result'])) {
        $desc = $json['description'] ?? 'unknown error';
        if (str_contains($desc, 'Conflict')) {
            echo "[Mirza Poller] Conflict detected, re-clearing webhook...\n";
            @file_get_contents("https://api.telegram.org/bot{$APIKEY}/deleteWebhook?drop_pending_updates=false");
        }
        sleep(2);
        continue;
    }

    foreach ($json['result'] as $item) {
        $updateId = $item['update_id'];
        $offset = $updateId + 1;

        $payload = json_encode($item, JSON_UNESCAPED_UNICODE);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $proc = proc_open('php ' . escapeshellarg(__DIR__ . '/index.php'), $descriptors, $pipes, __DIR__);

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
