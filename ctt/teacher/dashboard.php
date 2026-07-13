<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('teacher');

$teacher_id = $_SESSION['user_id'];

// Teacher Info
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Teacher WHERE TeacherID = $teacher_id"));

// Stats
$total_classes = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT COUNT(DISTINCT tc.ClassID) 
     FROM timetablecelldetail tcd 
     JOIN timetablecell tc ON tcd.TTCID = tc.TTCID 
     WHERE tcd.TeacherID = $teacher_id"))[0] ?? 0;

$total_subjects = mysqli_fetch_row(mysqli_query($conn, 
    "SELECT COUNT(DISTINCT SubjectID) 
     FROM timetablecelldetail 
     WHERE TeacherID = $teacher_id"))[0] ?? 0;

$today_day = date('l');

$today_classes = mysqli_query($conn, 
    "SELECT 
        p.StartTime, p.EndTime,
        sub.Name as SubjectName,
        c.Name as ClassName,
        sec.Name as SectionName,
        r.Title as RoomName
     FROM timetablecelldetail tcd
     JOIN timetablecell tc ON tcd.TTCID = tc.TTCID
     JOIN period p ON tc.PeriodID = p.PeriodID
     JOIN subject sub ON tcd.SubjectID = sub.SubjectID
     LEFT JOIN class c ON tc.ClassID = c.ClassID
     LEFT JOIN section sec ON tcd.SectionID = sec.SectionID
     LEFT JOIN room r ON tcd.RoomID = r.RoomID
     WHERE tcd.TeacherID = $teacher_id 
       AND tcd.Days = '$today_day'
     ORDER BY p.StartTime");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - CTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">

    <style>
        .welcome-card {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 20px; padding: 2rem; color: #fff;
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_teacher.php'; ?>

<main class="main-content" id="mainContent">

    <!-- Welcome -->
    <div class="welcome-card mb-4">
        <h3>Welcome back, <?= htmlspecialchars($teacher['Name']) ?>!</h3>
        <p>Today is <?= date('l, F j, Y') ?> — Here's your teaching overview.</p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff;">
                    <i class="fas fa-school" style="color:#2563eb;"></i>
                </div>
                <div class="stat-value"><?= $total_classes ?></div>
                <div class="stat-label">Classes</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4;">
                    <i class="fas fa-book" style="color:#059669;"></i>
                </div>
                <div class="stat-value"><?= $total_subjects ?></div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fffbeb;">
                    <i class="fas fa-clock" style="color:#d97706;"></i>
                </div>
                <div class="stat-value"><?= mysqli_num_rows($today_classes) ?></div>
                <div class="stat-label">Today’s Periods</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;">
                    <i class="fas fa-calendar-alt" style="color:#dc2626;"></i>
                </div>
                <div class="stat-value">—</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-4">
        <h6 class="fw-700 text-muted mb-3 text-uppercase" style="letter-spacing: 0.5px;">Quick Actions</h6>
        <div class="row g-3">
            <div class="col-xl-3 col-md-6 col-6">
                <a href="view_timetable.php" class="quick-action-card">
                    <div class="qa-icon" style="background:#eff6ff;"><i class="fas fa-calendar-week text-primary"></i></div>
                    <div>
                        <div class="qa-title">Weekly Schedule</div>
                        <div class="qa-sub">Full Timetable</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <a href="mark_attendance.php" class="quick-action-card">
                    <div class="qa-icon" style="background:#f0fdf4;"><i class="fas fa-clipboard-check text-success"></i></div>
                    <div>
                        
                        <div class="qa-sub">Today's Classes</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <a href="profile.php" class="quick-action-card">
                    <div class="qa-icon" style="background:#fefce8;"><i class="fas fa-user-edit text-warning"></i></div>
                    <div>
                        <div class="qa-title">My Profile</div>
                        <div class="qa-sub">Update Info</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 col-6">
                <a href="change_password.php" class="quick-action-card">
                    <div class="qa-icon" style="background:#f3e8ff;"><i class="fas fa-shield-alt text-purple"></i></div>
                    <div>
                        <div class="qa-title">Security</div>
                        <div class="qa-sub">Change Password</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Today's Schedule -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="data-table-card">
                <div class="data-table-header">
                    <div class="data-table-title"><i class="fas fa-clock me-2 text-primary"></i>Today's Schedule</div>
                    <a href="view_timetable.php" class="text-primary fw-bold">View Full Weekly Timetable →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Class - Section</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($today_classes) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">No classes scheduled for today.</td></tr>
                        <?php else: while ($row = mysqli_fetch_assoc($today_classes)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['StartTime']) ?> - <?= htmlspecialchars($row['EndTime']) ?></strong></td>
                                <td><strong><?= htmlspecialchars($row['SubjectName']) ?></strong></td>
                                <td><?= htmlspecialchars($row['ClassName'] ?? '-') ?> - <?= htmlspecialchars($row['SectionName'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['RoomName'] ?? '—') ?></td>
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