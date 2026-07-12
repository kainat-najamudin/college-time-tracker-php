<?php
$current = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['file' => 'dashboard.php',        'icon' => 'fa-tachometer-alt',       'label' => 'Dashboard'],
    ['file' => 'view_teachers.php',    'icon' => 'fa-chalkboard-teacher',   'label' => 'Teachers'],
    ['file' => 'view_students.php',    'icon' => 'fa-user-graduate',        'label' => 'Students'],
    ['file' => 'view_timetable.php',   'icon' => 'fa-calendar-alt',         'label' => 'Timetables'],
    ['file' => 'view_classes.php',     'icon' => 'fa-school',               'label' => 'Classes'],
    ['file' => 'view_subjects.php',    'icon' => 'fa-book',                 'label' => 'Subjects'],
    ['file' => 'view_rooms.php',       'icon' => 'fa-door-open',            'label' => 'Rooms'],
    ['file' => 'view_periods.php',     'icon' => 'fa-clock',                'label' => 'Periods'],
    ['file' => 'profile.php',          'icon' => 'fa-user-circle',          'label' => 'My Profile'],
    ['file' => 'change_password.php',  'icon' => 'fa-key',                  'label' => 'Change Password'],
];
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-clock me-2"></i>
        <span class="brand-text">CTT</span>
    </div>
    <div class="sidebar-role-badge">
        <span><i class="fas fa-user-tie me-1"></i>Academic In-Charge</span>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($navItems as $item): ?>
        <li class="nav-item <?= $current === $item['file'] ? 'active' : '' ?>">
            <a href="/incharge/<?= $item['file'] ?>" class="nav-link">
                <i class="fas <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        </li>
        <?php endforeach; ?>
        <li class="nav-item">
            <a href="/logout.php" class="nav-link" style="color:#ef4444;" onclick="return confirm('Logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
