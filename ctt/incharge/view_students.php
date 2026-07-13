<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$search  = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$filterD = mysqli_real_escape_string($conn, $_GET['dept']   ?? '');
$filterS = mysqli_real_escape_string($conn, $_GET['sem']    ?? '');

$where = "WHERE 1=1";
if ($search)  $where .= " AND (Name LIKE '%$search%' OR Email LIKE '%$search%' OR RollNo LIKE '%$search%')";
if ($filterD) $where .= " AND Department='$filterD'";
if ($filterS) $where .= " AND Semester='$filterS'";

$students = mysqli_query($conn, "SELECT * FROM Student $where ORDER BY Name");
$total    = mysqli_num_rows($students);
$depts    = mysqli_query($conn, "SELECT DISTINCT Department FROM Student WHERE Department != '' ORDER BY Department");
$sems     = mysqli_query($conn, "SELECT DISTINCT Semester FROM Student WHERE Semester != '' ORDER BY Semester");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - CTT In-Charge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_incharge.php'; ?>

<main class="main-content" id="mainContent">
    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Students</div>
            <h4><i class="fas fa-user-graduate me-2"></i>Students List</h4>
            <p>View all registered students</p>
        </div>
        <span style="background:#eff6ff;color:#2563eb;padding:8px 20px;border-radius:50px;font-weight:700;font-size:0.88rem;">
            <i class="fas fa-eye me-1"></i>View Only
        </span>
    </div>

    <!-- Filter -->
    <div class="form-card mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <div style="position:relative;">
                    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, roll no..." value="<?=htmlspecialchars($search)?>" style="padding-left:40px;">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="dept" class="form-select">
                    <option value="">All Departments</option>
                    <?php while($d=mysqli_fetch_assoc($depts)): ?>
                    <option value="<?=htmlspecialchars($d['Department'])?>" <?=$filterD==$d['Department']?'selected':''?>><?=htmlspecialchars($d['Department'])?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Semester</label>
                <select name="sem" class="form-select">
                    <option value="">All</option>
                    <?php while($s=mysqli_fetch_assoc($sems)): ?>
                    <option value="<?=htmlspecialchars($s['Semester'])?>" <?=$filterS==$s['Semester']?'selected':''?>><?=htmlspecialchars($s['Semester'])?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-primary-custom flex-fill"><i class="fas fa-filter me-2"></i>Filter</button>
                <a href="view_students.php" class="btn btn-light" style="border-radius:12px;padding:10px 16px;"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>

    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-user-graduate me-2" style="color:#d97706;"></i>All Students <span style="font-size:0.78rem;color:#94a3b8;">(<?=$total?>)</span></div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>#</th><th>Student</th><th>Roll No</th><th>Email</th><th>Department</th><th>Semester</th></tr></thead>
                <tbody>
                <?php if($total==0): ?>
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-user-graduate fa-2x d-block mb-2"></i>No students found</td></tr>
                <?php else: $i=1; while($s=mysqli_fetch_assoc($students)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;"><?=strtoupper(substr($s['Name'],0,1))?></div>
                            <div><div style="font-weight:700;"><?=htmlspecialchars($s['Name'])?></div><div style="font-size:0.75rem;color:#94a3b8;">ID: <?=$s['StudentID']?></div></div>
                        </div>
                    </td>
                    <td><span style="background:#fff3cd;color:#856404;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;"><?=htmlspecialchars($s['RollNo']??'—')?></span></td>
                    <td style="font-size:0.85rem;"><?=htmlspecialchars($s['Email']??'—')?></td>
                    <td><span style="background:#fffbeb;color:#d97706;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;"><?=htmlspecialchars($s['Department']??'—')?></span></td>
                    <td style="font-size:0.85rem;font-weight:600;color:#374151;"><?=htmlspecialchars($s['Semester']??'—')?></td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body>
</html>
