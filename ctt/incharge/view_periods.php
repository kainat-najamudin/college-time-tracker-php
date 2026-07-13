<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');
$items = mysqli_query($conn,"SELECT * FROM Period ORDER BY DisplayOrder");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Periods - CTT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head><body>
<?php include '../includes/header.php'; include '../includes/sidebar_incharge.php'; ?>
<main class="main-content" id="mainContent">
<div class="page-header">
    <div class="page-title">
        <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Periods</div>
        <h4><i class="fas fa-clock me-2"></i>Periods</h4>
    </div>
</div>
<div class="data-table-card">
    <div class="data-table-header"><div class="data-table-title"><i class="fas fa-clock me-2" style="color:#0284c7;"></i>All Periods</div></div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>#</th><th>Title</th><th>Start Time</th><th>End Time</th><th>Friday Start</th><th>Friday End</th><th>Type</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($items)==0): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No periods configured</td></tr>
        <?php else: $i=1; while($r=mysqli_fetch_assoc($items)): ?>
        <tr>
            <td><?=$i++?></td>
            <td><strong><?=htmlspecialchars($r['Title'])?></strong></td>
            <td><?=date('h:i A', strtotime($r['StartTime']))?></td>
            <td><?=date('h:i A', strtotime($r['EndTime']))?></td>
            <td><?=$r['FridayStartTime'] ? date('h:i A', strtotime($r['FridayStartTime'])) : '—'?></td>
            <td><?=$r['FridayEndTime']   ? date('h:i A', strtotime($r['FridayEndTime']))   : '—'?></td>
            <td>
                <?php if($r['IsBreak']): ?>
                    <span style="background:#fffbeb;color:#d97706;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;"><i class="fas fa-coffee me-1"></i>Break</span>
                <?php elseif($r['IsZeroPeriod']): ?>
                    <span style="background:#f5f3ff;color:#7c3aed;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;">Zero Period</span>
                <?php else: ?>
                    <span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;">Regular</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table></div>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body></html>
