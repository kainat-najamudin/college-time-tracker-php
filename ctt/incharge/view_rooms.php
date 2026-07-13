<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');
$items = mysqli_query($conn,"SELECT * FROM Room ORDER BY Title");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rooms - CTT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head><body>
<?php include '../includes/header.php'; include '../includes/sidebar_incharge.php'; ?>
<main class="main-content" id="mainContent">
<div class="page-header">
    <div class="page-title">
        <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Rooms</div>
        <h4><i class="fas fa-door-open me-2"></i>Rooms</h4>
    </div>
</div>
<div class="row g-3">
<?php if(mysqli_num_rows($items)==0): ?>
    <div class="col-12 text-center text-muted py-5"><i class="fas fa-door-open fa-3x d-block mb-3"></i>No rooms found</div>
<?php else: while($r=mysqli_fetch_assoc($items)): ?>
<div class="col-xl-3 col-md-4 col-6">
    <div style="background:#fff;border-radius:14px;padding:1.2rem;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);text-align:center;">
        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0284c7,#38bdf8);color:#fff;font-size:1.2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;"><i class="fas fa-door-open"></i></div>
        <div style="font-weight:800;color:#1e293b;"><?=htmlspecialchars($r['Title'])?></div>
        <?php if(!empty($r['Description'])): ?><div style="font-size:0.78rem;color:#94a3b8;margin-top:4px;"><?=htmlspecialchars($r['Description'])?></div><?php endif; ?>
    </div>
</div>
<?php endwhile; endif; ?>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body></html>
