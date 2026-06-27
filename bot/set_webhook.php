<?php
require_once __DIR__ . '/../includes/telegram.php';
$url = PANEL_URL . '/bot/webhook.php';
$res = tg_api('setWebhook', ['url'=>$url, 'allowed_updates'=>['message','callback_query']]);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['webhook_url'=>$url, 'response'=>$res], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
