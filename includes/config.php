<?php
// ============== ZYRON SMS PANEL CONFIG ==============

// رابط اللوحة الأصلية (مصدر الأرقام)
define('INTS_BASE', 'http://151.80.19.204/ints');
define('INTS_USER', 'Hama11');
define('INTS_PASS', 'Hama11');

// بوت تلغرام
define('TG_BOT_TOKEN', '8794957674:AAHNixVjIIcMRV14lP44nyzWxGxro8mOLh4');
define('TG_GROUP_ID', '-1003921031641'); // آيدي المجموعة لاستقبال الرموز

// رابط لوحتك بعد رفعها (بدون / في النهاية)
define('PANEL_URL', 'https://yourdomain.com');

// قاعدة البيانات (SQLite افتراضياً، لا تحتاج إعداد)
define('DB_PATH', __DIR__ . '/../data/panel.db');

// أمان الجلسة
define('SESSION_NAME', 'ZYRONSID');

// مهلة طلب الأرقام (ثواني)
define('REQUEST_COOLDOWN', 5);

// عدد الأرقام لكل دولة في الطلب
define('NUMBERS_PER_REQUEST', 4);

date_default_timezone_set('Asia/Riyadh');
