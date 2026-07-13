<?php
// College Time Tracker - Main Configuration

define('APP_NAME', 'College Time Tracker');
define('APP_SHORT_NAME', 'CTT System');
define('BASE_PATH', dirname(__DIR__));

// XAMPP default database settings. Update password if your MySQL has one.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ctt_db');

define('APP_TIMEZONE', 'Asia/Karachi');
date_default_timezone_set(APP_TIMEZONE);

// In development keep errors visible. For final deployment, set to 0.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
