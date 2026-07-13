<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$uid = (int)$_SESSION['user_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old  = md5($_POST['old_password']);
    $new  = $_POST['new_password'];
    $conf = $_POST['confirm_password'];

    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM AcademicsIncharge WHERE InchargeID=$uid"));
    if ($user['Password'] !== $old) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $conf) {
        $error = "New passwords do not match.";
    } else {
        $hashed = md5($new);
        mysqli_query($conn,"UPDATE AcademicsIncharge SET Password='$hashed' WHERE InchargeID=$uid")
            ? $success = "Password changed successfully!"
            : $error   = mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Change Password</div>
            <h4><i class="fas fa-key me-2"></i>Change Password</h4>
        </div>
    </div>

    <?php if($success): ?><div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?></div><?php endif; ?>
    <?php if($error):   ?><div class="alert alert-danger"  style="border-radius:12px;"><i class="fas fa-times-circle me-2"></i><?=$error?></div><?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="form-card">
                <div class="text-center mb-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#1a3c6e,#2563eb);color:#fff;font-size:1.6rem;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;"><i class="fas fa-key"></i></div>
                    <h5 style="font-weight:800;">Update Password</h5>
                    <p class="text-muted" style="font-size:0.85rem;">Enter your current password and choose a new one</p>
                </div>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div style="position:relative;">
                            <input type="password" name="old_password" id="oldPwd" class="form-control" required style="padding-right:44px;">
                            <i class="fas fa-eye" onclick="togglePwd('oldPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="newPwd" class="form-control" required minlength="6" style="padding-right:44px;" oninput="checkMatch()">
                            <i class="fas fa-eye" onclick="togglePwd('newPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                        </div>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <div style="position:relative;">
                            <input type="password" name="confirm_password" id="confPwd" class="form-control" required style="padding-right:44px;" oninput="checkMatch()">
                            <i class="fas fa-eye" onclick="togglePwd('confPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                        </div>
                        <div id="matchMsg" style="font-size:0.78rem;margin-top:4px;"></div>
                    </div>
                    <button type="submit" class="btn-primary-custom w-100"><i class="fas fa-save me-2"></i>Update Password</button>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function togglePwd(id, icon) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}
function checkMatch() {
    const n = document.getElementById('newPwd').value;
    const c = document.getElementById('confPwd').value;
    const msg = document.getElementById('matchMsg');
    if (!c) { msg.innerHTML = ''; return; }
    msg.innerHTML = n === c
        ? '<span style="color:#059669;"><i class="fas fa-check me-1"></i>Passwords match</span>'
        : '<span style="color:#dc2626;"><i class="fas fa-times me-1"></i>Passwords do not match</span>';
}
</script>
</body>
</html>
