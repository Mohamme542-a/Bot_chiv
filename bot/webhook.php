<?php
// Telegram Bot Webhook
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/countries.php';
require_once __DIR__ . '/../includes/telegram.php';

db_init();
$pdo = db();
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) { http_response_code(200); exit; }

function build_countries_kb() {
    $pdo = db();
    $rows = $pdo->query("SELECT country, cc, COUNT(*) c FROM numbers GROUP BY country ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC);
    $map = country_map();
    $kb = []; $row = [];
    foreach ($rows as $r) {
        $flag = $map[$r['cc']]['flag'] ?? '🌐';
        $row[] = ['text'=>"$flag {$r['country']} ({$r['c']})", 'callback_data'=>'C:'.$r['country']];
        if (count($row) === 2) { $kb[] = $row; $row = []; }
    }
    if ($row) $kb[] = $row;
    return $kb;
}

function build_numbers_text($country) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM numbers WHERE country=? ORDER BY RANDOM() LIMIT ?");
    $stmt->execute([$country, NUMBERS_PER_REQUEST]);
    $nums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$nums) return ["لا توجد أرقام لـ $country حالياً.", []];

    $svcMap = [];
    $q = $pdo->query("SELECT phone, service FROM messages WHERE id IN (SELECT MAX(id) FROM messages GROUP BY phone)");
    foreach ($q as $r) $svcMap[$r['phone']] = $r['service'];

    $text = "🌍 <b>$country</b>\nاضغط على الرقم لنسخه:\n\n";
    foreach ($nums as $n) {
        $svc = $svcMap[$n['phone']] ?? 'متاح';
        $text .= "📱 <code>{$n['phone']}</code>\n   └ <i>$svc</i>\n\n";
    }
    $kb = [
        [
            ['text'=>'🔄 أرقام جديدة', 'callback_data'=>'R:'.$country],
            ['text'=>'🌐 تغيير الدولة', 'callback_data'=>'BACK'],
        ]
    ];
    return [$text, $kb];
}

function get_tg_user($chat_id, $username='') {
    $pdo = db();
    $pdo->prepare("INSERT OR IGNORE INTO tg_users(chat_id,username) VALUES(?,?)")->execute([$chat_id,$username]);
    return $pdo->query("SELECT * FROM tg_users WHERE chat_id=$chat_id")->fetch(PDO::FETCH_ASSOC);
}

function can_request($chat_id) {
    $u = get_tg_user($chat_id);
    return (time() - (int)$u['last_request']) >= REQUEST_COOLDOWN;
}

function mark_request($chat_id, $country=null) {
    $pdo = db();
    $pdo->prepare("UPDATE tg_users SET last_request=?, current_country=? WHERE chat_id=?")
        ->execute([time(),$country,$chat_id]);
}

// === Handle message ===
if (isset($update['message'])) {
    $m = $update['message'];
    $chat = $m['chat']['id'];
    $user = $m['from']['username'] ?? '';
    get_tg_user($chat, $user);
    $text = trim($m['text'] ?? '');
    if ($text === '/start' || $text === '🌐 الدول') {
        $kb = build_countries_kb();
        if (!$kb) {
            tg_send($chat, "أهلاً! لا توجد أرقام متاحة حالياً، حاول لاحقاً.");
        } else {
            tg_send($chat, "🌍 <b>اختر الدولة:</b>\nسيتم إعطاؤك " . NUMBERS_PER_REQUEST . " أرقام عند الاختيار.\n⏱ يمكنك طلب أرقام جديدة كل " . REQUEST_COOLDOWN . " ثواني.", $kb);
        }
    } else {
        tg_send($chat, "اكتب /start لعرض الدول.");
    }
}

// === Handle callback ===
if (isset($update['callback_query'])) {
    $cq = $update['callback_query'];
    $chat = $cq['message']['chat']['id'];
    $mid = $cq['message']['message_id'];
    $data = $cq['data'];

    if ($data === 'BACK') {
        $kb = build_countries_kb();
        tg_edit($chat, $mid, "🌍 <b>اختر الدولة:</b>", $kb);
        tg_answer_callback($cq['id']);
    } elseif (strpos($data, 'C:') === 0 || strpos($data, 'R:') === 0) {
        $country = substr($data, 2);
        if (strpos($data,'R:')===0 && !can_request($chat)) {
            tg_answer_callback($cq['id'], "⏱ انتظر " . REQUEST_COOLDOWN . " ثواني قبل طلب أرقام جديدة");
        } else {
            mark_request($chat, $country);
            [$txt, $kb] = build_numbers_text($country);
            tg_edit($chat, $mid, $txt, $kb);
            tg_answer_callback($cq['id'], "✅");
        }
    }
}

http_response_code(200);
echo 'ok';
