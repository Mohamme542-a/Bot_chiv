<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/countries.php';

// جالب يسجل الدخول إلى لوحة ints ويسحب الأرقام والرسائل
class IntsScraper {
    private $cookieJar;
    public function __construct() {
        $this->cookieJar = sys_get_temp_dir() . '/zyron_ints_' . md5(INTS_USER) . '.txt';
    }

    private function curl($url, $post = null, $extraHeaders = []) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 ZyronPanel',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array_merge([
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ], $extraHeaders),
        ]);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
        }
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return ['body'=>$body, 'info'=>$info];
    }

    public function login() {
        // قد تحتاج لتعديل أسماء الحقول حسب نموذج موقعك
        $this->curl(INTS_BASE . '/login');
        $res = $this->curl(INTS_BASE . '/login', [
            'username' => INTS_USER,
            'password' => INTS_PASS,
        ]);
        return $res['info']['http_code'] < 400;
    }

    public function fetchNumbers() {
        $res = $this->curl(INTS_BASE . '/agent/MySMSNumbers');
        return $this->parseNumbers($res['body']);
    }

    public function fetchSMS() {
        $res = $this->curl(INTS_BASE . '/agent/SMSCDRStats');
        return $this->parseSMS($res['body']);
    }

    private function parseNumbers($html) {
        $out = [];
        if (!$html) return $out;
        if (preg_match_all('/(\+?\d{8,15})/', $html, $m)) {
            foreach (array_unique($m[1]) as $phone) {
                $info = detect_country($phone);
                $out[] = ['phone'=>$phone, 'country'=>$info['name'], 'cc'=>$info['cc']];
            }
        }
        return $out;
    }

    private function parseSMS($html) {
        $out = [];
        if (!$html) return $out;
        // محاولة عامة لاستخراج صفوف الجدول
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows)) {
            foreach ($rows[1] as $row) {
                if (!preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $tds)) continue;
                $cells = array_map(function($t){
                    return trim(html_entity_decode(strip_tags($t), ENT_QUOTES, 'UTF-8'));
                }, $tds[1]);
                if (count($cells) < 3) continue;
                // ابحث عن رقم هاتف داخل أي خلية
                $phone = ''; $text = ''; $sender = '';
                foreach ($cells as $c) {
                    if (!$phone && preg_match('/^\+?\d{8,15}$/', $c)) { $phone = $c; continue; }
                    if (!$sender && strlen($c) < 30 && !preg_match('/\d{4,}/', $c)) $sender = $c;
                    if (strlen($c) > strlen($text)) $text = $c;
                }
                if ($phone && $text) {
                    $out[] = ['phone'=>$phone,'sender'=>$sender,'text'=>$text,'time'=>time()];
                }
            }
        }
        return $out;
    }

    public function sync() {
        if (!$this->login()) return ['ok'=>false,'error'=>'login failed'];
        $nums = $this->fetchNumbers();
        $sms  = $this->fetchSMS();
        $pdo = db();
        $now = time();
        $ins = $pdo->prepare("INSERT OR IGNORE INTO numbers(phone,country,cc,last_seen) VALUES(?,?,?,?)");
        $upd = $pdo->prepare("UPDATE numbers SET last_seen=? WHERE phone=?");
        foreach ($nums as $n) {
            $ins->execute([$n['phone'],$n['country'],$n['cc'],$now]);
            $upd->execute([$now,$n['phone']]);
        }
        $insM = $pdo->prepare("INSERT OR IGNORE INTO messages(phone,sender,text,service,code,received_at) VALUES(?,?,?,?,?,?)");
        $newCodes = [];
        foreach ($sms as $s) {
            $service = detect_service($s['text']);
            $code = extract_code($s['text']);
            if ($insM->execute([$s['phone'],$s['sender'],$s['text'],$service,$code,$s['time']])
                && $pdo->lastInsertId()) {
                $newCodes[] = ['phone'=>$s['phone'],'service'=>$service,'code'=>$code,'text'=>$s['text']];
            }
        }
        return ['ok'=>true,'numbers'=>count($nums),'sms'=>count($sms),'new'=>$newCodes];
    }
}
