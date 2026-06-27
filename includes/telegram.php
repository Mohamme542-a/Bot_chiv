<?php
require_once __DIR__ . '/config.php';

function tg_api($method, $params = []) {
    $url = 'https://api.telegram.org/bot' . TG_BOT_TOKEN . '/' . $method;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function tg_send($chat_id, $text, $keyboard = null) {
    $params = ['chat_id'=>$chat_id, 'text'=>$text, 'parse_mode'=>'HTML'];
    if ($keyboard) $params['reply_markup'] = ['inline_keyboard'=>$keyboard];
    return tg_api('sendMessage', $params);
}

function tg_edit($chat_id, $message_id, $text, $keyboard = null) {
    $params = ['chat_id'=>$chat_id,'message_id'=>$message_id,'text'=>$text,'parse_mode'=>'HTML'];
    if ($keyboard) $params['reply_markup'] = ['inline_keyboard'=>$keyboard];
    return tg_api('editMessageText', $params);
}

function tg_answer_callback($id, $text = '') {
    return tg_api('answerCallbackQuery', ['callback_query_id'=>$id,'text'=>$text]);
}

function tg_broadcast_code($phone, $service, $code, $text) {
    if (!TG_GROUP_ID) return;
    $msg = "📩 <b>رمز جديد</b>\n"
         . "🌐 الخدمة: <b>$service</b>\n"
         . "📱 الرقم: <code>$phone</code>\n"
         . ($code ? "🔑 الكود: <code>$code</code>\n" : "")
         . "\n<i>" . htmlspecialchars(mb_substr($text,0,300)) . "</i>";
    tg_send(TG_GROUP_ID, $msg);
}
