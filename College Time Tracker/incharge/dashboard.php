<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('incharge');
dashboard_start('Academic In-Charge Dashboard','Manage teachers, students, schedules and reports');
?>
<div class="welcome-card mb-4"><h2 class="fw-bold">Welcome, <?= h(current_user_name()) ?>!</h2><p class="mb-0 opacity-75">Academic operations panel for College Time Tracker.</p></div>
<div class="row g-3 mb-4">
<?php stat_card('fa-chalkboard-teacher','Teachers',scalar_query('SELECT COUNT(*) FROM teacher'),'#7c3aed'); stat_card('fa-user-graduate','Students',scalar_query('SELECT COUNT(*) FROM student'),'#f59e0b'); stat_card('fa-calendar-days','Timetable Entries',scalar_query('SELECT COUNT(*) FROM timetablecelldetail'),'#059669'); stat_card('fa-book','Subjects',scalar_query('SELECT COUNT(*) FROM subject'),'#2563eb'); ?>
</div>
<div class="row g-3"><div class="col-md-4"><a class="quick-link" href="<?= app_url('incharge/users.php') ?>"><i class="fa-solid fa-users"></i>Teachers & Students</a></div><div class="col-md-4"><a class="quick-link" href="<?= app_url('incharge/timetable.php') ?>"><i class="fa-solid fa-calendar-days"></i>Manage Timetable</a></div><div class="col-md-4"><a class="quick-link" href="<?= app_url('incharge/reports.php') ?>"><i class="fa-solid fa-chart-line"></i>Reports</a></div></div>
<?php dashboard_end(); ?>
