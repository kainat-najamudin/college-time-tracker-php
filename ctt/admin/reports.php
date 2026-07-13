<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

// Summary stats
$stats = [
    'incharge' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM AcademicsIncharge"))[0],
    'teachers' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Teacher"))[0],
    'students' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Student"))[0],
    'programs' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Program"))[0],
    'classes'  => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Class"))[0],
    'subjects' => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Subject"))[0],
    'rooms'    => mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Room"))[0],
    'timetables'=> mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Timetable"))[0],
];

// Teacher workload
$teacher_workload = mysqli_query($conn,"
    SELECT t.Name, t.Department, t.Designation,
           COUNT(DISTINCT tcd.TTCDID) as TotalSlots,
           COUNT(DISTINCT tcd.Days) as DaysTeaching,
           GROUP_CONCAT(DISTINCT s.Name ORDER BY s.Name SEPARATOR ', ') as Subjects
    FROM Teacher t
    LEFT JOIN TimetableCellDetail tcd ON tcd.TeacherID = t.TeacherID
    LEFT JOIN Subject s ON s.SubjectID = tcd.SubjectID
    GROUP BY t.TeacherID
    ORDER BY TotalSlots DESC
");

// Students per department
$dept_stats = mysqli_query($conn,"SELECT Department, COUNT(*) as Total FROM Student WHERE Department!='' GROUP BY Department ORDER BY Total DESC");

// Students per semester
$sem_stats = mysqli_query($conn,"SELECT Semester, COUNT(*) as Total FROM Student WHERE Semester!='' GROUP BY Semester ORDER BY Total DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CTT Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        @media print {
            .sidebar, .top-navbar, .no-print { display: none !important; }
            .main-content { margin: 0 !important; padding: 1rem !important; }
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content" id="mainContent">
    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Reports</div>
            <h4><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
            <p>System overview and academic reports</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn-primary-custom" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Report</button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <?php
        $sCards = [
            ['label'=>'In-Charge',  'val'=>$stats['incharge'],  'icon'=>'fa-user-tie',          'bg'=>'#eff6ff',  'color'=>'#2563eb'],
            ['label'=>'Teachers',   'val'=>$stats['teachers'],   'icon'=>'fa-chalkboard-teacher', 'bg'=>'#f5f3ff',  'color'=>'#7c3aed'],
            ['label'=>'Students',   'val'=>$stats['students'],   'icon'=>'fa-user-graduate',      'bg'=>'#fffbeb',  'color'=>'#d97706'],
            ['label'=>'Programs',   'val'=>$stats['programs'],   'icon'=>'fa-graduation-cap',     'bg'=>'#fef2f2',  'color'=>'#dc2626'],
            ['label'=>'Classes',    'val'=>$stats['classes'],    'icon'=>'fa-school',             'bg'=>'#fff7ed',  'color'=>'#ea580c'],
            ['label'=>'Subjects',   'val'=>$stats['subjects'],   'icon'=>'fa-book',               'bg'=>'#ecfdf5',  'color'=>'#059669'],
            ['label'=>'Rooms',      'val'=>$stats['rooms'],      'icon'=>'fa-door-open',          'bg'=>'#f0f9ff',  'color'=>'#0284c7'],
            ['label'=>'Timetables', 'val'=>$stats['timetables'], 'icon'=>'fa-calendar-alt',       'bg'=>'#f0fdf4',  'color'=>'#059669'],
        ];
        foreach($sCards as $c): ?>
        <div class="col-xl-3 col-md-4 col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:<?=$c['bg']?>"><i class="fas <?=$c['icon']?>" style="color:<?=$c['color']?>;"></i></div>
                <div class="stat-value"><?=$c['val']?></div>
                <div class="stat-label"><?=$c['label']?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Teacher Workload -->
    <div class="data-table-card mb-4">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Teacher Workload Report</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>#</th><th>Teacher</th><th>Department</th><th>Designation</th><th>Total Slots</th><th>Days Teaching</th><th>Subjects Assigned</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($teacher_workload)==0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No workload data. Assign teachers to timetable cells first.</td></tr>
                <?php else: $i=1; while($row=mysqli_fetch_assoc($teacher_workload)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;flex-shrink:0;"><?=strtoupper(substr($row['Name'],0,1))?></div>
                            <strong><?=htmlspecialchars($row['Name'])?></strong>
                        </div>
                    </td>
                    <td><?=htmlspecialchars($row['Department']??'—')?></td>
                    <td><?=htmlspecialchars($row['Designation']??'—')?></td>
                    <td>
                        <?php $slots = $row['TotalSlots']; $bg = $slots > 20 ? '#fef2f2' : ($slots > 10 ? '#fffbeb' : '#f0fdf4'); $col = $slots > 20 ? '#dc2626' : ($slots > 10 ? '#d97706' : '#059669'); ?>
                        <span style="background:<?=$bg?>;color:<?=$col?>;padding:3px 12px;border-radius:50px;font-size:0.82rem;font-weight:700;"><?=$slots?> slots</span>
                    </td>
                    <td><span style="background:#eff6ff;color:#2563eb;padding:3px 12px;border-radius:50px;font-size:0.82rem;font-weight:600;"><?=$row['DaysTeaching']?> days</span></td>
                    <td style="font-size:0.82rem;color:#374151;max-width:250px;"><?=htmlspecialchars($row['Subjects']??'Not assigned')?></td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Students by Dept -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header"><div class="data-table-title"><i class="fas fa-building me-2 text-warning"></i>Students by Department</div></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>#</th><th>Department</th><th>Students</th><th>Share</th></tr></thead>
                        <tbody>
                        <?php if(mysqli_num_rows($dept_stats)==0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No department data</td></tr>
                        <?php else: $i=1; while($row=mysqli_fetch_assoc($dept_stats)): ?>
                        <tr>
                            <td><?=$i++?></td>
                            <td><strong><?=htmlspecialchars($row['Department'])?></strong></td>
                            <td><?=$row['Total']?></td>
                            <td style="min-width:120px;">
                                <?php $pct = round(($row['Total']/$stats['students'])*100); ?>
                                <div style="background:#f1f5f9;border-radius:50px;height:8px;overflow:hidden;">
                                    <div style="background:#2563eb;height:100%;width:<?=$pct?>%;border-radius:50px;"></div>
                                </div>
                                <small class="text-muted"><?=$pct?>%</small>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Students by Semester -->
        <div class="col-lg-6">
            <div class="data-table-card">
                <div class="data-table-header"><div class="data-table-title"><i class="fas fa-layer-group me-2" style="color:#7c3aed;"></i>Students by Semester</div></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>#</th><th>Semester</th><th>Students</th><th>Share</th></tr></thead>
                        <tbody>
                        <?php if(mysqli_num_rows($sem_stats)==0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No semester data</td></tr>
                        <?php else: $i=1; while($row=mysqli_fetch_assoc($sem_stats)): ?>
                        <tr>
                            <td><?=$i++?></td>
                            <td><span style="background:#f5f3ff;color:#7c3aed;padding:3px 12px;border-radius:50px;font-size:0.82rem;font-weight:600;"><?=htmlspecialchars($row['Semester'])?></span></td>
                            <td><?=$row['Total']?></td>
                            <td style="min-width:120px;">
                                <?php $pct = round(($row['Total']/$stats['students'])*100); ?>
                                <div style="background:#f1f5f9;border-radius:50px;height:8px;overflow:hidden;">
                                    <div style="background:#7c3aed;height:100%;width:<?=$pct?>%;border-radius:50px;"></div>
                                </div>
                                <small class="text-muted"><?=$pct?>%</small>
                            </td>
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
