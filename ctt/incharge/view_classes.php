<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$items = mysqli_query($conn,"SELECT c.*, p.Name as ProgramName FROM Class c LEFT JOIN Program p ON p.ProgramID=c.ProgramID ORDER BY c.Name");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Classes - CTT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head><body>
<?php include '../includes/header.php'; include '../includes/sidebar_incharge.php'; ?>
<main class="main-content" id="mainContent">
<div class="page-header">
    <div class="page-title">
        <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Classes</div>
        <h4><i class="fas fa-school me-2"></i>Classes</h4>
    </div>
</div>
<div class="data-table-card">
    <div class="data-table-header"><div class="data-table-title"><i class="fas fa-school me-2 text-danger"></i>All Classes</div></div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>#</th><th>Class Name</th><th>Program</th><th>Session</th><th>Current Semester</th><th>Status</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($items)==0): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No classes found</td></tr>
        <?php else: $i=1; while($r=mysqli_fetch_assoc($items)): ?>
        <tr>
            <td><?=$i++?></td>
            <td><strong><?=htmlspecialchars($r['Name'])?></strong></td>
            <td style="font-size:0.85rem;"><?=htmlspecialchars($r['ProgramName']??'—')?></td>
            <td style="font-size:0.85rem;"><?=htmlspecialchars($r['Session']??'—')?></td>
            <td><span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;"><?=htmlspecialchars($r['CurrentSemester']??'—')?></span></td>
            <td><span style="background:<?=$r['IsActive']?'#d1fae5':'#fee2e2'?>;color:<?=$r['IsActive']?'#065f46':'#991b1b'?>;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;"><?=$r['IsActive']?'Active':'Inactive'?></span></td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table></div>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body></html>
