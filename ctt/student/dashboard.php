<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('student');

$student_id = $_SESSION['user_id'];

$student = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT * FROM student WHERE StudentID = '$student_id'")
);

if(!$student){
    die("Student not found");
}

$student_name = $student['Name'];
$department = $student['Department'];
$semester = $student['Semester'];

$total_subjects = 0;
$total_classes = 0;
$attendance_percentage = 0;

$today = date('l');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - College Time Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="../assets/css/layout.css" rel="stylesheet">

<style>
.welcome-card{
    background: linear-gradient(135deg,#059669,#10b981);
    color:white;
    padding:30px;
    border-radius:20px;
}

.stat-card{
    background:white;
    border-radius:15px;
    padding:20px;
    text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.stat-icon{
    width:60px;
    height:60px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
    margin-bottom:10px;
}

.stat-value{
    font-size:28px;
    font-weight:700;
}

.stat-label{
    color:#6c757d;
}

.quick-action-card{
    display:flex;
    align-items:center;
    gap:15px;
    padding:20px;
    background:#fff;
    border-radius:15px;
    text-decoration:none;
    color:#000;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.quick-action-card:hover{
    transform:translateY(-3px);
}

.qa-icon{
    width:50px;
    height:50px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.data-table-card{
    background:#fff;
    border-radius:15px;
    padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.data-table-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}
</style>
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_student.php'; ?>

<main class="main-content" id="mainContent">

    <!-- Welcome Section -->
    <div class="welcome-card mb-4">
        <h3>
            Welcome Back,
            <?= htmlspecialchars($student_name) ?>!
        </h3>

        <p class="mb-0">
            <?= htmlspecialchars($department) ?>
            |
            <?= htmlspecialchars($semester) ?>
        </p>

        <small>
            Today is <?= date('l, F j, Y') ?>
        </small>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-subtle">
                    <i class="fas fa-book text-primary"></i>
                </div>

                <div class="stat-value">
                    <?= $total_subjects ?>
                </div>

                <div class="stat-label">
                    My Subjects
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-subtle">
                    <i class="fas fa-calendar-alt text-success"></i>
                </div>

                <div class="stat-value">
                    <?= $total_classes ?>
                </div>

                <div class="stat-label">
                    Today's Classes
                </div>
            </div>
        </div>

        

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger-subtle">
                    <i class="fas fa-bell text-danger"></i>
                </div>

                <div class="stat-value">
                    0
                </div>

                <div class="stat-label">
                    Notifications
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="mb-4">
        <h5 class="mb-3">Quick Actions</h5>

        <div class="row g-3">

            <div class="col-md-3">
                <a href="view_timetable.php" class="quick-action-card">
                    <div class="qa-icon bg-primary-subtle">
                        <i class="fas fa-calendar-week text-primary"></i>
                    </div>

                    <div>
                        <strong>My Timetable</strong><br>
                        <small>View Schedule</small>
                    </div>
                </a>
            </div>

            
      

            <div class="col-md-3">
                <a href="profile.php" class="quick-action-card">
                    <div class="qa-icon bg-warning-subtle">
                        <i class="fas fa-user text-warning"></i>
                    </div>

                    <div>
                        <strong>My Profile</strong><br>
                        <small>Update Info</small>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="change_password.php" class="quick-action-card">
                    <div class="qa-icon bg-danger-subtle">
                        <i class="fas fa-lock text-danger"></i>
                    </div>

                    <div>
                        <strong>Security</strong><br>
                        <small>Change Password</small>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <!-- Today's Schedule -->
    <div class="data-table-card">

        <div class="data-table-header">
            <h5>
                <i class="fas fa-clock text-primary"></i>
                Today's Schedule
            </h5>
        </div>

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Subject</th>
                        <th>Room</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            No classes scheduled for today
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/layout.js"></script>

</body>
</html>