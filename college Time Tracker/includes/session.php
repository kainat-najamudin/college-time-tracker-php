<?php
// includes/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE_URL fallback agar db.php pehle include na hua ho
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

// ─── Role Guard Functions ────────────────────────────────────────────────────
// login.php => $_SESSION['user_id'] + $_SESSION['role'] set karta hai
// Ye functions wahi check karte hain aur role-specific aliases bhi set karte hain

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    // Alias set karo taaki admin/ pages kaam karein
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['admin_id']   = $_SESSION['user_id'];
        $_SESSION['admin_name'] = $_SESSION['user_name'] ?? 'Admin';
    }
}

function requireIncharge() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'incharge') {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    // Alias set karo taaki incharge/ pages kaam karein
    if (!isset($_SESSION['incharge_id'])) {
        $_SESSION['incharge_id']   = $_SESSION['user_id'];
        $_SESSION['incharge_name'] = $_SESSION['user_name'] ?? 'In-Charge';
    }
}

function requireTeacher() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    // Alias set karo taaki teacher/ pages kaam karein
    if (!isset($_SESSION['teacher_id'])) {
        $_SESSION['teacher_id']   = $_SESSION['user_id'];
        $_SESSION['teacher_name'] = $_SESSION['user_name'] ?? 'Teacher';
    }
}

function requireStudent() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    // Alias set karo taaki student/ pages kaam karein
    if (!isset($_SESSION['student_id'])) {
        $_SESSION['student_id']   = $_SESSION['user_id'];
        $_SESSION['student_name'] = $_SESSION['user_name'] ?? 'Student';
    }
}

// ─── Backward Compatible requireRole() ──────────────────────────────────────

function requireRole($role = 'incharge') {
    switch ($role) {
        case 'admin':
            requireAdmin();
            break;
        case 'incharge':
            requireIncharge();
            break;
        case 'teacher':
            requireTeacher();
            break;
        case 'student':
            requireStudent();
            break;
        default:
            requireIncharge();
    }
}

// ─── Redirect If Already Logged In ──────────────────────────────────────────

function redirectIfLoggedIn() {
    if (!isset($_SESSION['user_id'])) return;

    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin')    { header('Location: ' . BASE_URL . 'admin/dashboard.php');    exit(); }
    if ($role === 'incharge') { header('Location: ' . BASE_URL . 'incharge/dashboard.php'); exit(); }
    if ($role === 'teacher')  { header('Location: ' . BASE_URL . 'teacher/dashboard.php');  exit(); }
    if ($role === 'student')  { header('Location: ' . BASE_URL . 'student/dashboard.php');  exit(); }
}

// ─── Get Logged-in User Info ─────────────────────────────────────────────────

function getLoggedInUser() {
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'role' => $_SESSION['role'] ?? '',
        'id'   => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? ''
    ];
}
