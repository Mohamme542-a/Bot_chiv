<?php
require_once __DIR__ . '/includes/scraper.php';
require_once __DIR__ . '/includes/telegram.php';
$s = new IntsScraper();
$res = $s->sync();
if (!empty($res['new'])) {
    foreach ($res['new'] as $n) {
        tg_broadcast_code($n['phone'],$n['service'],$n['code'],$n['text']);
    }
}
echo date('c') . " - " . json_encode($res, JSON_UNESCAPED_UNICODE) . "\n";
