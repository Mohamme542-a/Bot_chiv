<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pdo = db();
$rows = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="UTF-8"><title>الرسائل</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/style.css"></head><body>
<header class="topbar"><div class="brand">ZYRON <small>SMS</small></div>
<div class="user"><a href="dashboard.php">⬅ رجوع</a></div></header>
<main class="container">
<h2>آخر الرسائل والرموز</h2>
<table class="data">
<thead><tr><th>الوقت</th><th>الرقم</th><th>الخدمة</th><th>الكود</th><th>النص</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?=date('m-d H:i',$r['received_at'])?></td>
  <td><code><?=htmlspecialchars($r['phone'])?></code></td>
  <td><span class="badge"><?=htmlspecialchars($r['service'])?></span></td>
  <td><b><?=htmlspecialchars($r['code'])?></b></td>
  <td><?=htmlspecialchars(mb_substr($r['text'],0,120))?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</main></body></html>
