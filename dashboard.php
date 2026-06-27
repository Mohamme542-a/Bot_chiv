<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/countries.php';
require_login();

$pdo = db();
$total_numbers = $pdo->query("SELECT COUNT(*) FROM numbers")->fetchColumn();
$total_msgs = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$countries = $pdo->query("SELECT country, cc, COUNT(*) c FROM numbers GROUP BY country ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC);
$map = country_map();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة التحكم - ZYRON SMS</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="brand">ZYRON <small>SMS</small></div>
  <div class="user"><?=htmlspecialchars(current_user()['username'])?> · <a href="logout.php">خروج</a></div>
</header>
<nav class="tabs">
  <a class="active" href="dashboard.php">لوحة التحكم</a>
  <a href="numbers.php">الأرقام</a>
  <a href="messages.php">الرسائل</a>
  <a href="sync.php">مزامنة الآن</a>
</nav>
<main class="container">
  <div class="cards">
    <div class="stat"><div class="n"><?=$total_numbers?></div><div>إجمالي الأرقام</div></div>
    <div class="stat"><div class="n"><?=$total_msgs?></div><div>إجمالي الرسائل</div></div>
    <div class="stat"><div class="n"><?=count($countries)?></div><div>الدول</div></div>
  </div>
  <h2>النطاقات (الدول)</h2>
  <div class="country-grid">
  <?php foreach ($countries as $c):
      $flag = $map[$c['cc']]['flag'] ?? '🌐'; ?>
    <a class="country-card" href="numbers.php?country=<?=urlencode($c['country'])?>">
      <div class="flag"><?=$flag?></div>
      <div class="cname"><?=htmlspecialchars($c['country'])?></div>
      <div class="ccount"><?=$c['c']?> رقم</div>
    </a>
  <?php endforeach; ?>
  <?php if (!$countries): ?>
    <p>لا توجد أرقام بعد. اضغط "مزامنة الآن" لجلبها من اللوحة الأصلية.</p>
  <?php endif; ?>
  </div>
</main>
</body>
</html>
