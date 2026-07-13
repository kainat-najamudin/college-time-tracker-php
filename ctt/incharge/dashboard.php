<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$user = $_SESSION['user_name'];
$uid  = (int)$_SESSION['user_id'];

// Stats
$stats = [
    'teachers'   => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Teacher"))[0],
    'students'   => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Student"))[0],
    'classes'    => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Class WHERE IsActive=1"))[0],
    'subjects'   => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Subject"))[0],
    'timetables' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Timetable"))[0],
    'rooms'      => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Room"))[0],
];

// Recent teachers (last 5)
$recent_teachers = mysqli_query($conn,"SELECT * FROM Teacher ORDER BY TeacherID DESC LIMIT 5");

// Recent students (last 5)
$recent_students = mysqli_query($conn,"SELECT * FROM Student ORDER BY StudentID DESC LIMIT 5");

// Teacher workload summary
$workload = mysqli_query($conn,"
    SELECT t.Name, t.Department,
           COUNT(DISTINCT tcd.TTCDID) as Slots
    FROM Teacher t
    LEFT JOIN TimetableCellDetail tcd ON tcd.TeacherID = t.TeacherID
    GROUP BY t.TeacherID
    ORDER BY Slots DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-Charge Dashboard - CTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #1a3c6e 0%, #2563eb 50%, #7c3aed 100%);
            border-radius: 20px; padding: 2rem 2.5rem; color: #fff; position: relative; overflow: hidden;
        }
        .welcome-card::before {
            content: ''; position: absolute; right: -60px; top: -60px;
            width: 240px; height: 240px; border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .welcome-card::after {
            content: ''; position: absolute; right: 60px; bottom: -80px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .quick-card {
            background: #fff; border-radius: 16px;
            border: 1.5px solid #f1f5f9; padding: 1.4rem;
            text-align: center; cursor: pointer;
            transition: all 0.3s; text-decoration: none; display: block;
        }
        .quick-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-color: #2563eb; }
        .quick-card .qc-icon { width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 0.8rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .quick-card h6 { font-weight: 800; color: #1e293b; margin: 0; font-size: 0.88rem; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_incharge.php'; ?>

<main class="main-content" id="mainContent">

    <!-- Welcome Card -->
    <div class="welcome-card mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p style="opacity:0.7;margin:0;font-size:0.85rem;letter-spacing:1px;text-transform:uppercase;">Academic In-Charge Panel</p>
                <h3 style="font-weight:900;margin:0.3rem 0;">Welcome back, <?=htmlspecialchars($user)?>!</h3>
                <p style="opacity:0.75;margin:0;font-size:0.92rem;"><?=date('l, F d, Y')?> &nbsp;|&nbsp; Manage teachers, students and timetables.</p>
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <a href="view_teachers.php" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:50px;backdrop-filter:blur(10px);font-size:0.82rem;"><i class="fas fa-chalkboard-teacher me-1"></i>View Teachers</a>
                    <a href="view_students.php" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:50px;backdrop-filter:blur(10px);font-size:0.82rem;"><i class="fas fa-user-graduate me-1"></i>View Students</a>
                    <a href="view_timetable.php" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:50px;backdrop-filter:blur(10px);font-size:0.82rem;"><i class="fas fa-calendar-alt me-1"></i>Timetables</a>
                </div>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <div style="font-size:5rem;opacity:0.12;position:relative;z-index:1;"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['label'=>'Teachers',   'val'=>$stats['teachers'],   'icon'=>'fa-chalkboard-teacher','bg'=>'#f5f3ff','color'=>'#7c3aed','link'=>'view_teachers.php'],
            ['label'=>'Students',   'val'=>$stats['students'],   'icon'=>'fa-user-graduate',     'bg'=>'#fffbeb','color'=>'#d97706','link'=>'view_students.php'],
            ['label'=>'Classes',    'val'=>$stats['classes'],    'icon'=>'fa-school',            'bg'=>'#fef2f2','color'=>'#dc2626','link'=>'view_classes.php'],
            ['label'=>'Subjects',   'val'=>$stats['subjects'],   'icon'=>'fa-book',              'bg'=>'#ecfdf5','color'=>'#059669','link'=>'view_subjects.php'],
            ['label'=>'Timetables', 'val'=>$stats['timetables'], 'icon'=>'fa-calendar-alt',      'bg'=>'#eff6ff','color'=>'#2563eb','link'=>'view_timetable.php'],
            ['label'=>'Rooms',      'val'=>$stats['rooms'],      'icon'=>'fa-door-open',         'bg'=>'#f0f9ff','color'=>'#0284c7','link'=>'view_rooms.php'],
        ];
        foreach($statCards as $sc): ?>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="<?=$sc['link']?>" style="text-decoration:none;">
                <div class="stat-card" style="transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                    <div class="stat-icon" style="background:<?=$sc['bg']?>"><i class="fas <?=$sc['icon']?>" style="color:<?=$sc['color']?>;"></i></div>
                    <div class="stat-value"><?=$sc['val']?></div>
                    <div class="stat-label"><?=$sc['label']?></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Access -->
    <div class="row g-3 mb-4">
        <div class="col-12"><h6 style="font-weight:800;color:#1a3c6e;margin-bottom:0.5rem;"><i class="fas fa-bolt me-2 text-warning"></i>Quick Access</h6></div>
        <?php
        $quickLinks = [
            ['href'=>'view_timetable.php', 'icon'=>'fa-calendar-alt','bg'=>'linear-gradient(135deg,#1a3c6e,#2563eb)','label'=>'View Timetables'],
            ['href'=>'view_teachers.php',  'icon'=>'fa-chalkboard-teacher','bg'=>'linear-gradient(135deg,#5b21b6,#7c3aed)','label'=>'Teachers List'],
            ['href'=>'view_students.php',  'icon'=>'fa-user-graduate','bg'=>'linear-gradient(135deg,#d97706,#f59e0b)','label'=>'Students List'],
            ['href'=>'view_classes.php',   'icon'=>'fa-school','bg'=>'linear-gradient(135deg,#dc2626,#ef4444)','label'=>'Classes'],
            ['href'=>'view_subjects.php',  'icon'=>'fa-book','bg'=>'linear-gradient(135deg,#059669,#10b981)','label'=>'Subjects'],
            ['href'=>'view_periods.php',   'icon'=>'fa-clock','bg'=>'linear-gradient(135deg,#0284c7,#38bdf8)','label'=>'Periods'],
            ['href'=>'profile.php',        'icon'=>'fa-user-circle','bg'=>'linear-gradient(135deg,#475569,#64748b)','label'=>'My Profile'],
            ['href'=>'change_password.php','icon'=>'fa-key','bg'=>'linear-gradient(135deg,#0f766e,#14b8a6)','label'=>'Change Password'],
        ];
        foreach($quickLinks as $ql): ?>
        <div class="col-xl-3 col-md-4 col-6">
            <a href="<?=$ql['href']?>" class="quick-card">
                <div class="qc-icon" style="background:<?=$ql['bg']?>;color:#fff;"><i class="fas <?=$ql['icon']?>"></i></div>
                <h6><?=$ql['label']?></h6>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Row -->
    <div class="row g-4">

        <!-- Recent Teachers -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-chalkboard-teacher me-2" style="color:#7c3aed;"></i>Recent Teachers</div>
                    <a href="view_teachers.php" style="font-size:0.82rem;color:#2563eb;font-weight:600;text-decoration:none;">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Teacher</th><th>Department</th><th>Designation</th></tr></thead>
                        <tbody>
                        <?php if (mysqli_num_rows($recent_teachers)==0): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No teachers found</td></tr>
                        <?php else: while($t=mysqli_fetch_assoc($recent_teachers)): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.78rem;flex-shrink:0;"><?=strtoupper(substr($t['Name'],0,1))?></div>
                                    <span style="font-weight:600;font-size:0.85rem;"><?=htmlspecialchars($t['Name'])?></span>
                                </div>
                            </td>
                            <td style="font-size:0.82rem;"><?=htmlspecialchars($t['Department']??'—')?></td>
                            <td style="font-size:0.82rem;"><?=htmlspecialchars($t['Designation']??'—')?></td>
                        </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Teacher Workload -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-tasks me-2" style="color:#2563eb;"></i>Teacher Workload (Top 5)</div>
                </div>
                <div class="p-3">
                <?php if(mysqli_num_rows($workload)==0): ?>
                    <div class="text-center text-muted py-3">No timetable data yet</div>
                <?php else: while($w=mysqli_fetch_assoc($workload)):
                    $pct = min(100, ($w['Slots'] / 30) * 100); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span style="font-weight:700;font-size:0.85rem;color:#1e293b;"><?=htmlspecialchars($w['Name'])?></span>
                                <span style="font-size:0.75rem;color:#94a3b8;margin-left:6px;"><?=htmlspecialchars($w['Department']??'')?></span>
                            </div>
                            <span style="font-size:0.78rem;font-weight:700;color:#2563eb;"><?=$w['Slots']?> slots</span>
                        </div>
                        <div style="background:#f1f5f9;border-radius:50px;height:8px;overflow:hidden;">
                            <div style="background:linear-gradient(90deg,#2563eb,#7c3aed);height:100%;width:<?=$pct?>%;border-radius:50px;transition:width 1s;"></div>
                        </div>
                    </div>
                <?php endwhile; endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Students -->
        <div class="col-12">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-user-graduate me-2" style="color:#d97706;"></i>Recent Students</div>
                    <a href="view_students.php" style="font-size:0.82rem;color:#2563eb;font-weight:600;text-decoration:none;">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Student</th><th>Roll No</th><th>Department</th><th>Semester</th><th>Email</th></tr></thead>
                        <tbody>
                        <?php if(mysqli_num_rows($recent_students)==0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No students found</td></tr>
                        <?php else: while($st=mysqli_fetch_assoc($recent_students)): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.78rem;flex-shrink:0;"><?=strtoupper(substr($st['Name'],0,1))?></div>
                                    <span style="font-weight:600;font-size:0.85rem;"><?=htmlspecialchars($st['Name'])?></span>
                                </div>
                            </td>
                            <td><span style="background:#fff3cd;color:#856404;padding:2px 8px;border-radius:50px;font-size:0.78rem;font-weight:700;"><?=htmlspecialchars($st['RollNo']??'—')?></span></td>
                            <td style="font-size:0.82rem;"><?=htmlspecialchars($st['Department']??'—')?></td>
                            <td style="font-size:0.82rem;"><?=htmlspecialchars($st['Semester']??'—')?></td>
                            <td style="font-size:0.82rem;"><?=htmlspecialchars($st['Email']??'—')?></td>
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
