<?php
// includes/sidebar_teacher.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            
        </div>
        <button class="sidebar-toggle-btn d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-user">
    <!-- User Info Section -->
<!-- Teacher User Info -->
<div class="sidebar-user text-center py-4 border-bottom">
    <div class="user-avatar mx-auto" 
         style="width: 55px; height: 55px; 
                background: linear-gradient(135deg, #1e40af, #3b82f6); 
                color: #ffffff; 
                border-radius: 50%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                font-size: 1.6rem; 
                box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);">
        <i class="fas fa-chalkboard-teacher"></i>
    </div>
    <div class="user-info mt-3">
        <strong style="font-size: 1.05rem; color: #ffffff;">
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Teacher') ?>
        </strong> 
    </div>
</div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>teacher/dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-section-title">Timetable</li>

            <li class="nav-item <?php echo $current_page == 'view_timetable.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>teacher/view_timetable.php" class="nav-link">
                    <i class="fas fa-calendar-alt nav-icon"></i>
                    <span class="nav-text">My Timetable</span>
                </a>
            </li>

            <li class="nav-section-title">Account</li>

            <li class="nav-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>teacher/profile.php" class="nav-link">
                    <i class="fas fa-user-edit nav-icon"></i>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>

            <li class="nav-item <?php echo $current_page == 'change_password.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>teacher/change_password.php" class="nav-link">
                    <i class="fas fa-key nav-icon"></i>
                    <span class="nav-text">Change Password</span>
                </a>
            </li>

            <li class="nav-item mt-auto">
                <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
