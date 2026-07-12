<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-clock"></i></div>
        <div class="brand-text">
            <div class="brand-name">CTT System</div>
            <div class="brand-sub">Administrator Panel</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        <a href="/admin/dashboard.php" class="nav-item <?= $current=='dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>

        <div class="nav-section-title">User Management</div>
        <a href="/admin/manage_incharge.php" class="nav-item <?= $current=='manage_incharge.php' ? 'active' : '' ?>">
            <i class="fas fa-user-tie"></i><span>Academic In-Charge</span>
        </a>
        <a href="/admin/manage_teachers.php" class="nav-item <?= $current=='manage_teachers.php' ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-teacher"></i><span>Teachers</span>
        </a>
        <a href="/admin/manage_students.php" class="nav-item <?= $current=='manage_students.php' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i><span>Students</span>
        </a>

        <div class="nav-section-title">Academic Setup</div>
        <a href="/admin/manage_programs.php" class="nav-item <?= $current=='manage_programs.php' ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i><span>Programs</span>
        </a>
        <a href="/admin/manage_classes.php" class="nav-item <?= $current=='manage_classes.php' ? 'active' : '' ?>">
            <i class="fas fa-school"></i><span>Classes</span>
        </a>
        <a href="/admin/manage_subjects.php" class="nav-item <?= $current=='manage_subjects.php' ? 'active' : '' ?>">
            <i class="fas fa-book"></i><span>Subjects</span>
        </a>
        <a href="/admin/manage_sections.php" class="nav-item <?= $current=='manage_sections.php' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i><span>Sections</span>
        </a>
        <a href="/admin/manage_rooms.php" class="nav-item <?= $current=='manage_rooms.php' ? 'active' : '' ?>">
            <i class="fas fa-door-open"></i><span>Rooms</span>
        </a>
        <a href="/admin/manage_periods.php" class="nav-item <?= $current=='manage_periods.php' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i><span>Periods</span>
        </a>

        <div class="nav-section-title">Timetable</div>
        <a href="/admin/manage_timetable.php" class="nav-item <?= $current=='manage_timetable.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i><span>Timetables</span>
        </a>
        <a href="/admin/manage_timetable_detail.php" class="nav-item <?= $current=='manage_timetable_detail.php' ? 'active' : '' ?>">
            <i class="fas fa-th-list"></i><span>Timetable Details</span>
        </a>
<a href="/admin/view_final_timetable.php" class="nav-item">
    <i class="fas fa-eye"></i><span>View Final Timetable</span>
</a>
        <div class="nav-section-title">Reports & System</div>
        <a href="/admin/reports.php" class="nav-item <?= $current=='reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i><span>Reports</span>
        </a>
        <a href="/admin/backup.php" class="nav-item <?= $current=='backup.php' ? 'active' : '' ?>">
            <i class="fas fa-database"></i><span>DB Backup</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="/logout.php" class="nav-item text-danger-soft">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
</aside>