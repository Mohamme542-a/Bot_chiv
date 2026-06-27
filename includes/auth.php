<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: index.php');
        exit;
    }
}

function login($username, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user'] = ['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role']];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    session_destroy();
}

function new_captcha() {
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha'] = $a + $b;
    return "ما هو ناتج $a + $b ؟";
}

function check_captcha($answer) {
    return isset($_SESSION['captcha']) && (int)$answer === (int)$_SESSION['captcha'];
}
