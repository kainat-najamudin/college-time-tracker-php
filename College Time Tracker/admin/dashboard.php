<?php
require_once __DIR__ . '/../includes/layout.php';
//require_once __DIR__ . '/../includes/header.php';
require_role('admin');
$counts = [
  'Academic In-Charge'=>scalar_query('SELECT COUNT(*) FROM academicsincharge'),
  'Teachers'=>scalar_query('SELECT COUNT(*) FROM teacher'),
  'Students'=>scalar_query('SELECT COUNT(*) FROM student'),
  'Timetables'=>scalar_query('SELECT COUNT(*) FROM timetable'),
  'Programs'=>scalar_query('SELECT COUNT(*) FROM program'),
  'Classes'=>scalar_query('SELECT COUNT(*) FROM `class`'),
  'Subjects'=>scalar_query('SELECT COUNT(*) FROM subject'),
  'Rooms'=>scalar_query('SELECT COUNT(*) FROM room')
];
$recentTeachers = fetch_all('SELECT Name,Department,Designation,CreatedAt FROM teacher ORDER BY CreatedAt DESC LIMIT 5');
$recentStudents = fetch_all('SELECT Name,RollNo,Department,Semester,CreatedAt FROM student ORDER BY CreatedAt DESC LIMIT 5');
dashboard_start('Admin Dashboard','System overview and quick actions');
?>
<div class="welcome-card mb-4"><h2 class="fw-bold mb-1">Welcome back, <?= h(current_user_name()) ?>!</h2><p class="mb-0 opacity-75">Today is <?= date('l, F j, Y') ?>. Manage your College Time Tracker from here.</p></div>
<div class="row g-3 mb-4">
<?php $icons=['Academic In-Charge'=>'fa-user-tie','Teachers'=>'fa-chalkboard-teacher','Students'=>'fa-user-graduate','Timetables'=>'fa-calendar-days','Programs'=>'fa-graduation-cap','Classes'=>'fa-school','Subjects'=>'fa-book','Rooms'=>'fa-door-open']; foreach($counts as $label=>$value) stat_card($icons[$label],$label,$value); ?>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-3 col-6"><a class="quick-link" href="<?= app_url('admin/users.php') ?>"><i class="fa-solid fa-user-plus"></i>Add Users</a></div>
  <div class="col-md-3 col-6"><a class="quick-link" href="<?= app_url('admin/entities.php') ?>"><i class="fa-solid fa-layer-group"></i>Entities</a></div>
  <div class="col-md-3 col-6"><a class="quick-link" href="<?= app_url('admin/timetable.php') ?>"><i class="fa-solid fa-calendar-days"></i>Timetable</a></div>
  <div class="col-md-3 col-6"><a class="quick-link" href="<?= app_url('admin/reports.php') ?>"><i class="fa-solid fa-chart-line"></i>Reports</a></div>
</div>
<div class="row g-4">
  <div class="col-lg-6"><div class="data-card"><h5 class="fw-bold mb-3"><i class="fa-solid fa-chalkboard-teacher me-2 text-primary"></i>Recent Teachers</h5><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Department</th><th>Designation</th></tr></thead><tbody><?php foreach($recentTeachers as $t): ?><tr><td><?= h($t['Name']) ?></td><td><?= h($t['Department']) ?></td><td><?= h($t['Designation']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-6"><div class="data-card"><h5 class="fw-bold mb-3"><i class="fa-solid fa-user-graduate me-2 text-warning"></i>Recent Students</h5><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Name</th><th>Roll No</th><th>Semester</th></tr></thead><tbody><?php foreach($recentStudents as $s): ?><tr><td><?= h($s['Name']) ?></td><td><?= h($s['RollNo']) ?></td><td><?= h($s['Semester']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>


<?php dashboard_end();

?>
  