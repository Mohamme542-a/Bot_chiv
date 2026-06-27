<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/countries.php';
require_login();

$country = $_GET['country'] ?? '';
$pdo = db();
if ($country) {
    $stmt = $pdo->prepare("SELECT * FROM numbers WHERE country=? ORDER BY last_seen DESC");
    $stmt->execute([$country]);
} else {
    $stmt = $pdo->query("SELECT * FROM numbers ORDER BY last_seen DESC LIMIT 500");
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// آخر خدمة لكل رقم
$svcMap = [];
$q = $pdo->query("SELECT phone, service FROM messages WHERE id IN (SELECT MAX(id) FROM messages GROUP BY phone)");
foreach ($q as $r) $svcMap[$r['phone']] = $r['service'];
$map = country_map();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl"><head>
<meta charset="UTF-8"><title>الأرقام</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/style.css"></head><body>
<header class="topbar"><div class="brand">ZYRON <small>SMS</small></div>
<div class="user"><a href="dashboard.php">⬅ رجوع</a></div></header>
<main class="container">
<h2>الأرقام <?= $country ? " - $country" : '' ?></h2>
<table class="data">
<thead><tr><th>الدولة</th><th>الرقم</th><th>الخدمة الأخيرة</th><th>آخر ظهور</th></tr></thead>
<tbody>
<?php foreach ($rows as $r):
  $flag = $map[$r['cc']]['flag'] ?? '🌐';
  $svc = $svcMap[$r['phone']] ?? '-'; ?>
  <tr>
    <td><?=$flag?> <?=htmlspecialchars($r['country'])?></td>
    <td><code><?=htmlspecialchars($r['phone'])?></code></td>
    <td><span class="badge"><?=htmlspecialchars($svc)?></span></td>
    <td><?=$r['last_seen'] ? date('Y-m-d H:i',$r['last_seen']) : '-'?></td>
  </tr>
<?php endforeach; ?>
</tbody></table>
</main></body></html>
