<?php
// Mirza Bot Polling Worker for NOVA-X-PANEL
declare(strict_types=1);

date_default_timezone_set('Asia/Tehran');
ini_set('memory_limit', '512M');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/botapi.php';

global $APIKEY;

if (empty($APIKEY) || $APIKEY === '{API_KEY}') {
    fwrite(STDERR, "[Mirza Poller] APIKEY is not set in config.php. Exiting.\n");
    exit(1);
}

// Clear webhook so getUpdates receives incoming messages
echo "[Mirza Poller] Clearing existing webhook...\n";
$delResp = @file_get_contents("https://api.telegram.org/bot{$APIKEY}/deleteWebhook?drop_pending_updates=false");
echo "[Mirza Poller] deleteWebhook result: " . ($delResp ?: 'ok') . "\n";
echo "[Mirza Poller] Worker started. Listening for updates...\n";

$offset = 0;
while (true) {
    $url = "https://api.telegram.org/bot{$APIKEY}/getUpdates?offset={$offset}&timeout=20";
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 30,
            'ignore_errors' => true,
        ]
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        sleep(2);
        continue;
    }

    $json = json_decode($raw, true);
    if (!isset($json['ok']) || !$json['ok'] || !isset($json['result']) || !is_array($json['result'])) {
        sleep(2);
        continue;
    }

    foreach ($json['result'] as $item) {
        $updateId = $item['update_id'];
        $offset = $updateId + 1;

        $payload = json_encode($item, JSON_UNESCAPED_UNICODE);
        $proc = proc_open('php ' . escapeshellarg(__DIR__ . '/index.php'), [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);

        if (is_resource($proc)) {
            fwrite($pipes[0], $payload);
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($proc);
        }
    }
}
