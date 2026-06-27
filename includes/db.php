<?php
require_once __DIR__ . '/config.php';

function db() {
    static $pdo = null;
    if ($pdo === null) {
        if (!is_dir(dirname(DB_PATH))) mkdir(dirname(DB_PATH), 0775, true);
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("PRAGMA journal_mode=WAL;");
    }
    return $pdo;
}

function db_init() {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        created_at INTEGER
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS numbers(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT UNIQUE,
        country TEXT,
        cc TEXT,
        last_seen INTEGER
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT,
        sender TEXT,
        text TEXT,
        service TEXT,
        code TEXT,
        received_at INTEGER,
        UNIQUE(phone, text, received_at)
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS tg_users(
        chat_id INTEGER PRIMARY KEY,
        username TEXT,
        last_request INTEGER DEFAULT 0,
        current_country TEXT
    );");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_msg_phone ON messages(phone);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_num_country ON numbers(country);");
}
