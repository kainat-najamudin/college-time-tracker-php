<?php
require_once __DIR__ . '/functions.php';

function page_head($title, $bodyClass = '') {
    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . h($title) . ' - ' . APP_SHORT_NAME . '</title>';
    echo '<link rel="icon" href="' . app_url('assets/favicon.svg') . '">';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '<link href="' . app_url('assets/css/style.css') . '" rel="stylesheet">';
    echo '</head><body class="' . h($bodyClass) . '">';
}

function page_scripts() {
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>';
    echo '<script src="' . app_url('assets/js/app.js') . '"></script>';
    echo '</body></html>';
}

function public_nav() {
    echo '<nav class="navbar navbar-expand-lg navbar-dark ctt-navbar sticky-top"><div class="container">';
    echo '<a class="navbar-brand fw-bold" href="' . app_url('index.php') . '"><i class="fa-solid fa-clock me-2"></i>CTT <span>System</span></a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>';
    echo '<div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">';
    echo '<li class="nav-item"><a class="nav-link" href="' . app_url('index.php#features') . '">Features</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . app_url('index.php#roles') . '">Roles</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="' . app_url('index.php#about') . '">About</a></li>';
    echo '<li class="nav-item"><a class="btn btn-warning fw-bold ms-lg-2" href="' . app_url('login.php') . '">Login</a></li>';
    echo '</ul></div></div></nav>';
}

function sidebar_links($role) {
    $common = [];
    if ($role === 'admin') {
        return [
            ['Dashboard','admin/dashboard.php','fa-gauge-high'],
            ['Users','admin/users.php','fa-users'],
            ['Entities','admin/entities.php','fa-layer-group'],
            ['Timetable','admin/timetable.php','fa-calendar-days'],
            ['Reports','admin/reports.php','fa-chart-line'],
            ['Backup','admin/backup.php','fa-database'],
        ];
    }
    if ($role === 'incharge') {
        return [
            ['Dashboard','incharge/dashboard.php','fa-gauge-high'],
            ['Teachers & Students','incharge/users.php','fa-users'],
            ['Timetable','incharge/timetable.php','fa-calendar-days'],
            ['Reports','incharge/reports.php','fa-chart-line'],
        ];
    }
    if ($role === 'teacher') {
        return [['My Timetable','teacher/dashboard.php','fa-calendar-check']];
    }
    if ($role === 'student') {
        return [['My Timetable','student/dashboard.php','fa-calendar-check']];
    }
    return $common;
}
function dashboard_start($title, $subtitle = '') {
    require_login();
    $role = current_role();
    $user_name = current_user_name();
    $role_labels = [
        'admin'    => 'Administrator',
        'incharge' => 'Academic In-Charge',
        'teacher'  => 'Teacher',
        'student'  => 'Student',
    ];
    $role_label = $role_labels[$role] ?? 'User';

    page_head($title, 'dashboard-body');
    echo '<div class="dashboard-shell">';
    echo '<aside class="sidebar" id="sidebar"><a class="sidebar-brand" href="' . app_url(role_dashboard_path($role)) . '"><i class="fa-solid fa-clock"></i><span>CTT</span></a>';
    echo '<div class="sidebar-role">' . h(ucfirst($role)) . ' Panel</div><nav class="sidebar-nav">';
    foreach (sidebar_links($role) as $link) {
        echo '<a href="' . app_url($link[1]) . '"><i class="fa-solid ' . h($link[2]) . '"></i><span>' . h($link[0]) . '</span></a>';
    }
    echo '</nav><a class="logout-link" href="' . app_url('logout.php') . '"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></aside>';
    echo '<main class="dashboard-main"><header class="dashboard-topbar">';
    echo '<div><h1>' . h($title) . '</h1><p>' . h($subtitle) . '</p></div>';

    echo '<div class="d-flex align-items-center gap-3">';
    echo '<div class="navbar-time" id="navTime" style="font-size:0.9rem;color:#6b7280;font-weight:500;"></div>';
    echo '<div class="user-dropdown-wrap" style="position:relative;">';
    echo '<div class="user-pill" onclick="toggleUserDropdown()" style="cursor:pointer;">';
    echo '<div class="user-avatar-circle" style="width:34px;height:34px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">' . strtoupper(substr($user_name, 0, 1)) . '</div>';
    echo '<div class="d-none d-md-block" style="line-height:1.2;">';
    echo '<div style="font-weight:600;font-size:0.9rem;">' . h($user_name) . '</div>';
    echo '<div style="font-size:0.75rem;color:#6b7280;">' . h($role_label) . '</div>';
    echo '</div>';
    echo '<i class="fa-solid fa-chevron-down" style="font-size:0.7rem;color:#9ca3af;"></i>';
    echo '</div>';

    echo '<div id="userDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:200px;z-index:9999;overflow:hidden;">';
    echo '<div style="padding:12px 16px;border-bottom:1px solid #f3f4f6;">';
    echo '<div style="font-weight:700;font-size:0.9rem;">' . h($user_name) . '</div>';
    echo '<div style="font-size:0.78rem;color:#9ca3af;">' . h($role_label) . '</div>';
    echo '</div>';
    echo '<a href="' . app_url($role . '/profile.php') . '" style="display:flex;align-items:center;gap:10px;padding:10px 16px;color:#374151;text-decoration:none;font-size:0.88rem;" onmouseover="this.style.background=\'#f9fafb\'" onmouseout="this.style.background=\'\'"><i class="fa-solid fa-user-circle" style="color:#2563eb;width:16px;"></i>My Profile</a>';
    echo '<a href="' . app_url($role . '/change_password.php') . '" style="display:flex;align-items:center;gap:10px;padding:10px 16px;color:#374151;text-decoration:none;font-size:0.88rem;" onmouseover="this.style.background=\'#f9fafb\'" onmouseout="this.style.background=\'\'"><i class="fa-solid fa-key" style="color:#7c3aed;width:16px;"></i>Change Password</a>';
    echo '<div style="border-top:1px solid #f3f4f6;"></div>';
    echo '<a href="' . app_url('logout.php') . '" style="display:flex;align-items:center;gap:10px;padding:10px 16px;color:#dc2626;text-decoration:none;font-size:0.88rem;" onmouseover="this.style.background=\'#fff5f5\'" onmouseout="this.style.background=\'\'"><i class="fa-solid fa-sign-out-alt" style="width:16px;"></i>Logout</a>';
    echo '</div>';
    echo '</div></div>';

    echo '</header><section class="dashboard-content">';
    render_flash();
}

function dashboard_end() {
    echo '</section></main></div>';
    echo '<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("sidebar-hidden");
    }
    function toggleUserDropdown() {
        var m = document.getElementById("userDropdownMenu");
        m.style.display = m.style.display === "none" ? "block" : "none";
    }
    document.addEventListener("click", function(e) {
        if (!e.target.closest(".user-dropdown-wrap")) {
            var m = document.getElementById("userDropdownMenu");
            if (m) m.style.display = "none";
        }
    });
    function updateTime() {
        var el = document.getElementById("navTime");
        if (!el) return;
        el.textContent = new Date().toLocaleTimeString("en-US", {hour:"2-digit",minute:"2-digit",second:"2-digit"});
    }
    setInterval(updateTime, 1000); updateTime();
    </script>';
    page_scripts();
}

function stat_card($icon, $label, $value, $color = '#2563eb') {
    echo '<div class="col-xl-3 col-md-6"><div class="stat-card"><div class="stat-icon" style="background:' . h($color) . '18;color:' . h($color) . '"><i class="fa-solid ' . h($icon) . '"></i></div><div><div class="stat-value">' . h($value) . '</div><div class="stat-label">' . h($label) . '</div></div></div></div>';
}
