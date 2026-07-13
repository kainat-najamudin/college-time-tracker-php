<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

// Fetch counts
$total_incharge = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM AcademicsIncharge"))[0];
$total_teachers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Teacher"))[0];
$total_students = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Student"))[0];
$total_programs = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Program"))[0];
$total_classes  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Class"))[0];
$total_subjects = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Subject"))[0];
$total_rooms    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Room"))[0];
$total_tt       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Timetable"))[0];

// Recent teachers
$recent_teachers = mysqli_query($conn, "SELECT * FROM Teacher ORDER BY CreatedAt DESC LIMIT 5");
// Recent students
$recent_students = mysqli_query($conn, "SELECT * FROM Student ORDER BY CreatedAt DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #1a3c6e 0%, #2563eb 100%);
            border-radius: 20px; padding: 2rem; color: #fff;
            position: relative; overflow: hidden; margin-bottom: 1.5rem;
        }
        .welcome-card::before {
            content: ''; position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.05); border-radius: 50%;
            top: -100px; right: -80px;
        }
        .welcome-card::after {
            content: ''; position: absolute;
            width: 200px; height: 200px;
            background: rgba(245,158,11,0.1); border-radius: 50%;
            bottom: -80px; right: 100px;
        }
        .welcome-card h3 { font-weight: 800; font-size: 1.6rem; margin: 0; }
        .welcome-card p  { opacity: 0.8; margin: 0.4rem 0 0; }
        .welcome-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .btn-welcome {
            background: rgba(255,255,255,0.15); color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px; padding: 0.6rem 1.2rem;
            font-size: 0.88rem; font-weight: 600;
            text-decoration: none; transition: all 0.3s;
        }
        .btn-welcome:hover { background: rgba(255,255,255,0.25); color: #fff; }
        .btn-welcome.accent { background: #f59e0b; border-color: #f59e0b; }
        .btn-welcome.accent:hover { background: #d97706; }

        .quick-action-card {
            background: #fff; border-radius: 14px; padding: 1.2rem;
            border: 1px solid #f1f5f9; transition: all 0.3s; text-decoration: none;
            display: flex; align-items: center; gap: 1rem; height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .quick-action-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .qa-icon {
            width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .qa-title { font-weight: 700; font-size: 0.88rem; color: #1e293b; }
        .qa-sub   { font-size: 0.78rem; color: #64748b; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content" id="mainContent">

    <!-- Welcome -->
    <div class="welcome-card">
        <div style="position:relative; z-index:2;">
            <h3>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h3>
            <p>Today is <?= date('l, F j, Y') ?> — Here is your system overview.</p>
            <div class="welcome-actions">
                <a href="/ctt/admin/manage_incharge.php" class="btn-welcome accent"><i class="fas fa-plus me-1"></i>Add In-Charge</a>
                <a href="/ctt/admin/manage_timetable.php" class="btn-welcome"><i class="fas fa-calendar-alt me-1"></i>Manage Timetable</a>
                <a href="/ctt/admin/reports.php" class="btn-welcome"><i class="fas fa-chart-bar me-1"></i>View Reports</a>
                <a href="/ctt/admin/backup.php" class="btn-welcome"><i class="fas fa-database me-1"></i>DB Backup</a>
            </div>
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff;">
                    <i class="fas fa-user-tie" style="color:#2563eb;"></i>
                </div>
                <div class="stat-value"><?= $total_incharge ?></div>
                <div class="stat-label">Academic In-Charge</div>
                <div class="stat-change up"><i class="fas fa-arrow-up me-1"></i>Total registered</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f5f3ff;">
                    <i class="fas fa-chalkboard-teacher" style="color:#7c3aed;"></i>
                </div>
                <div class="stat-value"><?= $total_teachers ?></div>
                <div class="stat-label">Teachers</div>
                <div class="stat-change up"><i class="fas fa-arrow-up me-1"></i>Total registered</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fffbeb;">
                    <i class="fas fa-user-graduate" style="color:#d97706;"></i>
                </div>
                <div class="stat-value"><?= $total_students ?></div>
                <div class="stat-label">Students</div>
                <div class="stat-change up"><i class="fas fa-arrow-up me-1"></i>Total enrolled</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4;">
                    <i class="fas fa-calendar-alt" style="color:#059669;"></i>
                </div>
                <div class="stat-value"><?= $total_tt ?></div>
                <div class="stat-label">Timetables</div>
                <div class="stat-change up"><i class="fas fa-check me-1"></i>Active timetables</div>
            </div>
        </div>
    </div>

    <!-- Stats Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;">
                    <i class="fas fa-graduation-cap" style="color:#dc2626;"></i>
                </div>
                <div class="stat-value"><?= $total_programs ?></div>
                <div class="stat-label">Programs</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff7ed;">
                    <i class="fas fa-school" style="color:#ea580c;"></i>
                </div>
                <div class="stat-value"><?= $total_classes ?></div>
                <div class="stat-label">Classes</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf5;">
                    <i class="fas fa-book" style="color:#059669;"></i>
                </div>
                <div class="stat-value"><?= $total_subjects ?></div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0f9ff;">
                    <i class="fas fa-door-open" style="color:#0284c7;"></i>
                </div>
                <div class="stat-value"><?= $total_rooms ?></div>
                <div class="stat-label">Rooms</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-4">
        <h6 class="fw-700 text-muted mb-3" style="font-size:0.82rem; text-transform:uppercase; letter-spacing:0.05em;">Quick Actions</h6>
        <div class="row g-3">
            <?php
            $actions = [
                ['icon'=>'fa-user-tie',          'bg'=>'#eff6ff', 'color'=>'#2563eb',  'title'=>'Add In-Charge',  'sub'=>'Create new account', 'link'=>'/ctt/admin/manage_incharge.php'],
                ['icon'=>'fa-chalkboard-teacher', 'bg'=>'#f5f3ff', 'color'=>'#7c3aed',  'title'=>'Add Teacher',    'sub'=>'Register faculty',   'link'=>'/ctt/admin/manage_teachers.php'],
                ['icon'=>'fa-user-graduate',      'bg'=>'#fffbeb', 'color'=>'#d97706',  'title'=>'Add Student',    'sub'=>'Enroll student',     'link'=>'/ctt/admin/manage_students.php'],
                ['icon'=>'fa-graduation-cap',     'bg'=>'#fef2f2', 'color'=>'#dc2626',  'title'=>'Add Program',    'sub'=>'Create program',     'link'=>'/ctt/admin/manage_programs.php'],
                ['icon'=>'fa-school',             'bg'=>'#fff7ed', 'color'=>'#ea580c',  'title'=>'Add Class',      'sub'=>'Create class',       'link'=>'/ctt/admin/manage_classes.php'],
                ['icon'=>'fa-book',               'bg'=>'#ecfdf5', 'color'=>'#059669',  'title'=>'Add Subject',    'sub'=>'Assign subject',     'link'=>'/ctt/admin/manage_subjects.php'],
                ['icon'=>'fa-door-open',          'bg'=>'#f0f9ff', 'color'=>'#0284c7',  'title'=>'Add Room',       'sub'=>'Register room',      'link'=>'/ctt/admin/manage_rooms.php'],
                ['icon'=>'fa-calendar-alt',       'bg'=>'#f0fdf4', 'color'=>'#059669',  'title'=>'New Timetable',  'sub'=>'Build schedule',     'link'=>'/ctt/admin/manage_timetable.php'],
            ];
            foreach ($actions as $a): ?>
            <div class="col-xl-3 col-md-4 col-6">
                <a href="<?= $a['link'] ?>" class="quick-action-card">
                    <div class="qa-icon" style="background:<?= $a['bg'] ?>;">
                        <i class="fas <?= $a['icon'] ?>" style="color:<?= $a['color'] ?>;"></i>
                    </div>
                    <div>
                        <div class="qa-title"><?= $a['title'] ?></div>
                        <div class="qa-sub"><?= $a['sub'] ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Tables -->
    <div class="row g-4">
        <!-- Recent Teachers -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Recent Teachers</div>
                    <a href="/ctt/admin/manage_teachers.php" style="color:#2563eb; font-size:0.85rem; font-weight:600; text-decoration:none;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($recent_teachers) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No teachers found</td></tr>
                        <?php else: $i=1; while ($t = mysqli_fetch_assoc($recent_teachers)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                            <?= strtoupper(substr($t['Name'],0,1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.88rem;"><?= htmlspecialchars($t['Name']) ?></div>
                                            <div style="font-size:0.75rem;color:#9ca3af;"><?= htmlspecialchars($t['Username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($t['Department'] ?: '-') ?></td>
                                <td>
                                    <a href="/ctt/admin/manage_teachers.php?edit=<?= $t['TeacherID'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Students -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-user-graduate me-2" style="color:#d97706;"></i>Recent Students</div>
                    <a href="/ctt/admin/manage_students.php" style="color:#2563eb; font-size:0.85rem; font-weight:600; text-decoration:none;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Roll No</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($recent_students) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No students found</td></tr>
                        <?php else: $i=1; while ($s = mysqli_fetch_assoc($recent_students)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                            <?= strtoupper(substr($s['Name'],0,1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.88rem;"><?= htmlspecialchars($s['Name']) ?></div>
                                            <div style="font-size:0.75rem;color:#9ca3af;"><?= htmlspecialchars($s['Department'] ?: '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($s['RollNo']) ?></td>
                                <td><?= htmlspecialchars($s['Semester'] ?: '-') ?></td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body>
</html>
