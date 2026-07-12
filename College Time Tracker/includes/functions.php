<?php
require_once __DIR__ . '/db.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function app_url($path = '') {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
    $basePath = str_replace('\\', '/', realpath(BASE_PATH));
    $base = '';
    if ($docRoot && strpos($basePath, $docRoot) === 0) {
        $base = substr($basePath, strlen($docRoot));
    }
    $base = '/' . trim($base, '/');
    $base = $base === '/' ? '' : $base;
    return $base . '/' . ltrim($path, '/');
}

function redirect_to($path) {
    header('Location: ' . app_url($path));
    exit;
}

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_role() {
    start_secure_session();
    return $_SESSION['role'] ?? null;
}

function current_user_id() {
    start_secure_session();
    return $_SESSION['user_id'] ?? null;
}

function current_user_name() {
    start_secure_session();
    return $_SESSION['user_name'] ?? 'User';
}

function role_dashboard_path($role) {
    switch ($role) {
        case 'admin': return 'admin/dashboard.php';
        case 'incharge': return 'incharge/dashboard.php';
        case 'teacher': return 'teacher/dashboard.php';
        case 'student': return 'student/dashboard.php';
        default: return 'login.php';
    }
}

function require_login() {
    start_secure_session();
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        redirect_to('login.php');
    }
}

function require_role($role) {
    require_login();
    if (current_role() !== $role) {
        redirect_to(role_dashboard_path(current_role()));
    }
}

function require_any_role(array $roles) {
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        redirect_to(role_dashboard_path(current_role()));
    }
}

function password_matches($plain, $stored) {
    if (password_get_info($stored)['algo'] !== 0 && password_verify($plain, $stored)) {
        return true;
    }
    return md5($plain) === $stored;
}

function password_for_storage($plain) {
    return password_hash($plain, PASSWORD_DEFAULT);
}

function login_user($username, $password) {
    global $conn;
    $tables = [
        'admin' => ['table' => 'administrator', 'id' => 'AdminID'],
        'incharge' => ['table' => 'academicsincharge', 'id' => 'InchargeID'],
        'teacher' => ['table' => 'teacher', 'id' => 'TeacherID'],
        'student' => ['table' => 'student', 'id' => 'StudentID'],
    ];

    foreach ($tables as $role => $meta) {
        $sql = "SELECT * FROM `{$meta['table']}` WHERE Username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($user && password_matches($password, $user['Password'])) {
            start_secure_session();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user[$meta['id']];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $user['Email'];
            return $role;
        }
    }
    return false;
}

function scalar_query($sql) {
    global $conn;
    $res = mysqli_query($conn, $sql);
    if (!$res) return 0;
    $row = mysqli_fetch_row($res);
    return $row ? $row[0] : 0;
}

function fetch_all($sql) {
    global $conn;
    $rows = [];
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function format_time_short($time) {
    if (!$time) return '';
    return date('h:i', strtotime($time));
}

function split_days($days) {
    $parts = preg_split('/[-,\s]+/', strtoupper((string)$days));
    return array_values(array_filter(array_unique($parts)));
}

function has_day_overlap($a, $b) {
    return count(array_intersect(split_days($a), split_days($b))) > 0;
}

function check_timetable_conflict($ttid, $periodId, $teacherId, $roomId, $days, $ignoreDetailId = 0) {
    global $conn;
    $sql = "SELECT d.TTCDID, d.Days, s.Name AS SubjectName, t.Name AS TeacherName, r.Title AS RoomTitle, c.Name AS ClassName
            FROM timetablecelldetail d
            JOIN timetablecell cell ON cell.TTCID = d.TTCID
            LEFT JOIN subject s ON s.SubjectID = d.SubjectID
            LEFT JOIN teacher t ON t.TeacherID = d.TeacherID
            LEFT JOIN room r ON r.RoomID = d.RoomID
            LEFT JOIN `class` c ON c.ClassID = cell.ClassID
            WHERE cell.TTID = ? AND cell.PeriodID = ? AND d.TTCDID <> ? AND (d.TeacherID = ? OR d.RoomID = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iiiii', $ttid, $periodId, $ignoreDetailId, $teacherId, $roomId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $conflicts = [];
    while ($row = mysqli_fetch_assoc($res)) {
        if (has_day_overlap($days, $row['Days'])) {
            $conflicts[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $conflicts;
}

function get_or_create_cell($ttid, $classId, $periodId) {
    global $conn;
    $stmt = mysqli_prepare($conn, 'SELECT TTCID FROM timetablecell WHERE TTID=? AND ClassID=? AND PeriodID=? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'iii', $ttid, $classId, $periodId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if ($row) return (int)$row['TTCID'];

    $stmt = mysqli_prepare($conn, 'INSERT INTO timetablecell (TTID, ClassID, PeriodID) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'iii', $ttid, $classId, $periodId);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $id;
}

function add_flash($type, $message) {
    start_secure_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function render_flash() {
    start_secure_session();
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">' . h($flash['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

function export_csv($filename, array $headers, array $rows) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
