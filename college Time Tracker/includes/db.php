<?php
require_once __DIR__ . '/config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:Arial;padding:25px;background:#fff3f3;color:#991b1b;border:1px solid #fecaca;border-radius:12px;max-width:760px;margin:40px auto;">'
        . '<h2>Database connection failed</h2>'
        . '<p>Please start MySQL in XAMPP, create database <b>' . DB_NAME . '</b>, and import <b>database/ctt_db.sql</b> from phpMyAdmin.</p>'
        . '<pre>' . htmlspecialchars(mysqli_connect_error()) . '</pre>'
        . '</div>');
}

mysqli_set_charset($conn, 'utf8mb4');
