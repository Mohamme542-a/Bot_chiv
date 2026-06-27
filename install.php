<?php
require_once __DIR__ . '/includes/db.php';
db_init();
$pdo = db();
$exists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if (!$exists) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users(username,password,role,created_at) VALUES(?,?,?,?)")
        ->execute(['admin',$hash,'admin',time()]);
    echo "✅ تم إنشاء قاعدة البيانات والمستخدم: admin / admin123";
} else {
    echo "ℹ️ قاعدة البيانات جاهزة مسبقاً.";
}
echo "<br><br>⚠️ احذف ملف install.php بعد الانتهاء.";
