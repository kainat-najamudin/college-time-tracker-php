<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('incharge');

$uid = (int)current_user_id();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $old  = $_POST['old_password'] ?? '';
    $new  = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';

    $user = fetch_all("SELECT Password FROM academicsincharge WHERE InchargeID=$uid")[0] ?? [];

    if (!password_matches($old, $user['Password'] ?? '')) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $conf) {
        $error = "New passwords do not match.";
    } else {
        $hashed = mysqli_real_escape_string($conn, password_for_storage($new));
        if (mysqli_query($conn, "UPDATE academicsincharge SET Password='$hashed' WHERE InchargeID=$uid")) {
            $success = "Password changed successfully!";
        } else {
            $error = mysqli_error($conn);
        }
    }
}

dashboard_start('Change Password', 'Update your account password');
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= h($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= h($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="data-card">
            <div class="text-center mb-4">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#1a3c6e,#2563eb);color:#fff;font-size:1.6rem;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h5 class="fw-bold">Update Password</h5>
                <p class="text-muted" style="font-size:0.85rem;">Enter your current password and choose a new one</p>
            </div>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-500">Current Password</label>
                    <div style="position:relative;">
                        <input type="password" name="old_password" id="oldPwd" class="form-control" required style="padding-right:44px;">
                        <i class="fa-solid fa-eye" onclick="togglePwd('oldPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="new_password" id="newPwd" class="form-control" required minlength="6" style="padding-right:44px;" oninput="checkMatch()">
                        <i class="fa-solid fa-eye" onclick="togglePwd('newPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                    </div>
                    <div class="form-text">Minimum 6 characters</div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-500">Confirm New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="confirm_password" id="confPwd" class="form-control" required style="padding-right:44px;" oninput="checkMatch()">
                        <i class="fa-solid fa-eye" onclick="togglePwd('confPwd',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
                    </div>
                    <div id="matchMsg" style="font-size:0.78rem;margin-top:4px;"></div>
                </div>
                <button type="submit" class="btn btn-primary w-100 px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>
</div>

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
        ? '<span style="color:#059669;"><i class="fa-solid fa-check me-1"></i>Passwords match</span>'
        : '<span style="color:#dc2626;"><i class="fa-solid fa-times me-1"></i>Passwords do not match</span>';
}
</script>

<?php dashboard_end(); ?>