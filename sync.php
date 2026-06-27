<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/scraper.php';
require_once __DIR__ . '/includes/telegram.php';
require_login();

$s = new IntsScraper();
$res = $s->sync();
if (!empty($res['new'])) {
    foreach ($res['new'] as $n) {
        tg_broadcast_code($n['phone'],$n['service'],$n['code'],$n['text']);
    }
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($res, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
