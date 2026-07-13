<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$items = mysqli_query($conn,"SELECT s.*, c.Name as ClassName FROM Subject s LEFT JOIN Class c ON c.ClassID=s.ClassID ORDER BY c.Name, s.Name");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subjects - CTT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head><body>
<?php include '../includes/header.php'; include '../includes/sidebar_incharge.php'; ?>
<main class="main-content" id="mainContent">
<div class="page-header">
    <div class="page-title">
        <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Subjects</div>
        <h4><i class="fas fa-book me-2"></i>Subjects</h4>
    </div>
</div>
<div class="data-table-card">
    <div class="data-table-header"><div class="data-table-title"><i class="fas fa-book me-2 text-success"></i>All Subjects</div></div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>#</th><th>Subject Name</th><th>Class</th><th>Semester</th><th>Display Order</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($items)==0): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No subjects found</td></tr>
        <?php else: $i=1; while($r=mysqli_fetch_assoc($items)): ?>
        <tr>
            <td><?=$i++?></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-weight:800;font-size:0.8rem;display:flex;align-items:center;justify-content:center;"><?=strtoupper(substr($r['Name'],0,2))?></div>
                    <strong><?=htmlspecialchars($r['Name'])?></strong>
                </div>
            </td>
            <td><span style="background:#f0fdf4;color:#059669;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;"><?=htmlspecialchars($r['ClassName']??'—')?></span></td>
            <td style="font-size:0.85rem;"><?=htmlspecialchars($r['Semester']??'—')?></td>
            <td style="font-size:0.85rem;text-align:center;"><?=$r['DisplayOrder']??'—'?></td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table></div>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body></html>
