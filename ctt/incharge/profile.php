<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$uid  = (int)$_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM AcademicsIncharge WHERE InchargeID=$uid"));
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $dept  = mysqli_real_escape_string($conn, trim($_POST['department']));

    if (empty($name) || empty($email)) { $error = "Name and Email are required."; }
    else {
        mysqli_query($conn,"UPDATE AcademicsIncharge SET Name='$name', Email='$email', Department='$dept' WHERE InchargeID=$uid")
            ? ($success = "Profile updated." and $_SESSION['user_name'] = $name)
            : $error = mysqli_error($conn);
        $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM AcademicsIncharge WHERE InchargeID=$uid"));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Profile</div>
            <h4><i class="fas fa-user-circle me-2"></i>My Profile</h4>
        </div>
    </div>

    <?php if($success): ?><div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?></div><?php endif; ?>
    <?php if($error):   ?><div class="alert alert-danger"  style="border-radius:12px;"><i class="fas fa-times-circle me-2"></i><?=$error?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="form-card text-center">
                <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#1a3c6e,#2563eb);color:#fff;font-size:2.2rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <?=strtoupper(substr($user['Name'],0,1))?>
                </div>
                <h5 style="font-weight:800;"><?=htmlspecialchars($user['Name'])?></h5>
                <p style="color:#64748b;font-size:0.85rem;"><?=htmlspecialchars($user['Email']??'')?></p>
                <span style="background:#eff6ff;color:#2563eb;padding:5px 16px;border-radius:50px;font-size:0.82rem;font-weight:700;"><i class="fas fa-user-tie me-1"></i>Academic In-Charge</span>
                <div class="mt-3" style="background:#f8faff;border-radius:12px;padding:1rem;text-align:left;">
                    <div style="font-size:0.78rem;color:#94a3b8;margin-bottom:4px;">Department</div>
                    <div style="font-weight:700;color:#1a3c6e;"><?=htmlspecialchars($user['Department']??'Not set')?></div>
                </div>
                <div class="mt-2" style="background:#f8faff;border-radius:12px;padding:1rem;text-align:left;">
                    <div style="font-size:0.78rem;color:#94a3b8;margin-bottom:4px;">Username</div>
                    <div style="font-weight:700;color:#1a3c6e;"><?=htmlspecialchars($user['Username']??'')?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="form-card">
                <h6 style="font-weight:800;color:#1a3c6e;margin-bottom:1.5rem;"><i class="fas fa-edit me-2 text-primary"></i>Edit Profile</h6>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['Name'])?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($user['Email']??'')?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="<?=htmlspecialchars($user['Department']??'')?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?=htmlspecialchars($user['Username']??'')?>" disabled style="background:#f8faff;">
                            <div class="form-text">Username cannot be changed.</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body>
</html>
