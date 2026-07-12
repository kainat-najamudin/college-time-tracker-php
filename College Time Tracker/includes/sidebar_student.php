<?php
// includes/sidebar_student.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <!-- Optional logo -->
        </div>

        <button class="sidebar-toggle-btn d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="sidebar-user text-center py-4 border-bottom">

        <div class="user-avatar mx-auto"
            style="width: 55px; height: 55px;
                   background: linear-gradient(135deg, #16a34a, #22c55e);
                   color: #ffffff;
                   border-radius: 50%;
                   display: flex;
                   align-items: center;
                   justify-content: center;
                   font-size: 1.6rem;
                   box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);">

            <i class="fas fa-user-graduate"></i>
        </div>

        <div class="user-info mt-3">
            <strong style="font-size: 1.05rem; color: #ffffff;">
                <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>
            </strong>
        </div>

    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav-list">

            <!-- Dashboard -->
            <li class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>student/dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-section-title">Academic</li>

            <!-- Timetable -->
            <li class="nav-item <?= $current_page == 'view_timetable.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>student/view_timetable.php" class="nav-link">
                    <i class="fas fa-calendar-alt nav-icon"></i>
                    <span class="nav-text">My Timetable</span>
                </a>
            </li>

           

            <li class="nav-section-title">Account</li>

            <!-- Profile -->
            <li class="nav-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>student/profile.php" class="nav-link">
                    <i class="fas fa-user nav-icon"></i>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>

            <!-- Change Password -->
            <li class="nav-item <?= $current_page == 'change_password.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>student/change_password.php" class="nav-link">
                    <i class="fas fa-key nav-icon"></i>
                    <span class="nav-text">Change Password</span>
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item mt-auto">
                <a href="<?= BASE_URL ?>logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>

        </ul>
    </nav>

</div>