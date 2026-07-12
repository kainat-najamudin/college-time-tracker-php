<?php
require_once __DIR__ . '/includes/layout.php';
start_secure_session();
if (!empty($_SESSION['user_id'])) {
    redirect_to(role_dashboard_path($_SESSION['role']));
}
page_head('Home');
public_nav();
?>
<section class="hero d-flex align-items-center">
  <div class="container position-relative py-5" style="z-index:2;">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge mb-3"><i class="fa-solid fa-star"></i> Academic Management Platform</div>
        <h1 class="hero-title">College <span>Time</span><br>Tracker System</h1>
        <p class="hero-subtitle mt-4">A complete PHP and MySQL based academic scheduling system for administrators, academic in-charges, teachers, and students. Manage users, classes, rooms, periods, subjects, and conflict-free timetables from one centralized platform.</p>
        <div class="d-flex gap-3 flex-wrap mt-4">
          <a class="btn btn-primary btn-hero" href="<?= app_url('login.php') ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Get Started</a>
          <a class="btn btn-outline-light btn-hero" href="#features"><i class="fa-solid fa-circle-info me-2"></i>Learn More</a>
        </div>
        <div class="row g-3 mt-4">
          <div class="col-4"><div class="hero-card text-center"><h3 class="fw-bold text-warning mb-0">4</h3><small>User Roles</small></div></div>
          <div class="col-4"><div class="hero-card text-center"><h3 class="fw-bold text-warning mb-0">7</h3><small>Periods</small></div></div>
          <div class="col-4"><div class="hero-card text-center"><h3 class="fw-bold text-warning mb-0">0</h3><small>Conflicts</small></div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-card">
          <div class="text-center my-4">
   <img src="assets/images/time-tracker-clock.png" 
     class="img-fluid rounded-4 shadow" 
     style="max-height: 480px;" 
     alt="Time Tracker">
</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="features" id="features">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-badge mb-3"><i class="fa-solid fa-bolt"></i> Key Features</div>
      <h2 class="section-title">Everything Your College Needs</h2>
      <p class="text-muted mx-auto" style="max-width:680px;line-height:1.8;">Built for final-year project requirements with professional UI, secure role access, scheduling, reports, conflict checks, export, and print options.</p>
    </div>
    <div class="row g-4">
      <?php
      $features = [
        ['fa-calendar-alt','Timetable Management','Create class-wise timetable grids with periods, days, subjects, teachers, rooms, and sections.'],
        ['fa-users','User Management','Manage Academic In-Charge, Teacher, and Student accounts with role-based dashboards.'],
        ['fa-shield-halved','Role-Based Access','Administrator, In-Charge, Teacher, and Student each receive controlled access.'],
        ['fa-ban','Conflict Prevention','Checks teacher and room overlaps for same day and period before saving timetable entries.'],
        ['fa-print','Print & Export','Generate printable timetable, export CSV, and download grid image for reports.'],
        ['fa-chart-line','Reports','View teacher workload, room utilization, and academic summary reports.'],
      ];
      foreach ($features as $f): ?>
      <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="fa-solid <?= $f[0] ?>"></i></div><h5 class="fw-bold text-primary"><?= h($f[1]) ?></h5><p class="text-muted mb-0"><?= h($f[2]) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="roles" id="roles">
  <div class="container">
    <div class="text-center mb-5"><div class="section-badge mb-3">User Roles</div><h2 class="section-title">Role Specific Dashboards</h2></div>
    <div class="row g-4">
      <div class="col-lg-3 col-md-6"><div class="role-card role-admin"><i class="fa-solid fa-user-shield fa-3x mb-3"></i><h4>Administrator</h4><p>Full system control, users, timetable, reports, backup.</p></div></div>
      <div class="col-lg-3 col-md-6"><div class="role-card role-incharge"><i class="fa-solid fa-user-tie fa-3x mb-3"></i><h4>Academic In-Charge</h4><p>Manage teachers, students, classes, and timetables.</p></div></div>
      <div class="col-lg-3 col-md-6"><div class="role-card role-teacher"><i class="fa-solid fa-chalkboard-teacher fa-3x mb-3"></i><h4>Teacher</h4><p>View personal timetable and teaching workload.</p></div></div>
      <div class="col-lg-3 col-md-6"><div class="role-card role-student"><i class="fa-solid fa-user-graduate fa-3x mb-3"></i><h4>Student</h4><p>View class timetable and academic schedule.</p></div></div>
    </div>
  </div>
</section>

<section class="features" id="about">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6"><div class="panel-card p-4"><h3 class="fw-bold text-primary">Project Technology</h3><ul class="list-unstyled mb-0"><li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i>PHP backend logic</li><li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i>MySQL/MariaDB database</li><li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i>phpMyAdmin import-ready SQL</li><li class="mb-2"><i class="fa-solid fa-check-circle text-success me-2"></i>Bootstrap 5 responsive UI</li><li><i class="fa-solid fa-check-circle text-success me-2"></i>XAMPP local server ready</li></ul></div></div>
      <div class="col-lg-6"><div class="section-badge mb-3">About CTT</div><h2 class="section-title">Built for Academic Excellence</h2><p class="text-muted" style="line-height:1.9;">The College Time Tracker system eliminates manual timetable record keeping and reduces scheduling conflicts. It stores academic entities in a relational database and provides real-time access through dashboards.</p></div>
    </div>
  </div>
</section>

<footer class="py-5" style="background:#0f2444;color:#b8c7dc;"><div class="container"><div class="row g-4"><div class="col-lg-6"><h4 class="text-white fw-bold"><i class="fa-solid fa-clock me-2 text-warning"></i>CTT System</h4><p>College Time Tracker - Professional PHP final-year-project application.</p></div><div class="col-lg-3"><h6 class="text-white">Quick Links</h6><a class="d-block text-decoration-none text-light opacity-75" href="<?= app_url('login.php') ?>">Login</a><a class="d-block text-decoration-none text-light opacity-75" href="<?= app_url('signup.php') ?>">Sign Up</a></div><div class="col-lg-3"><h6 class="text-white">Contact</h6><p class="mb-1">admin@ctt.edu.pk</p><p>Pakistan</p></div></div><hr><p class="text-center mb-0">&copy; <?= date('Y') ?> College Time Tracker. All rights reserved.</p></div></footer>
<?php page_scripts(); ?>
