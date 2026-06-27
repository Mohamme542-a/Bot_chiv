<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_captcha($_POST['captcha'] ?? '')) {
        $error = 'إجابة الكابتشا غير صحيحة';
    } elseif (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'بيانات الدخول غير صحيحة';
    }
}
$captcha = new_captcha();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ZYRON SMS - تسجيل الدخول</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">
<div class="login-card">
  <div class="logo">
    <div class="logo-zr">Z<span>R</span></div>
    <div class="logo-name">ZYRON <small>SMS</small></div>
  </div>
  <p class="hint">يرجى تسجيل الدخول للمتابعة.</p>
  <?php if ($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <div class="field"><span>👤</span><input name="username" placeholder="اسم المستخدم" required></div>
    <div class="field"><span>🔒</span><input name="password" type="password" placeholder="كلمة المرور" required></div>
    <div class="captcha-row">
      <input name="captcha" placeholder="إجابة" required>
      <label><?=htmlspecialchars($captcha)?></label>
    </div>
    <button class="btn-login">تسجيل الدخول</button>
  </form>
</div>
</body>
</html>
