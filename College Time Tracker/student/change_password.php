<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('student');

$uid = (int)current_user_id();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $current  = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $user = fetch_all("SELECT Password FROM student WHERE StudentID=$uid")[0] ?? [];

    if (!password_matches($current, $user['Password'] ?? '')) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = mysqli_real_escape_string($conn, password_for_storage($new_pass));
        if (mysqli_query($conn, "UPDATE student SET Password='$hashed' WHERE StudentID=$uid")) {
            $success = "Password changed successfully!";
        } else {
            $error = mysqli_error($conn);
        }
    }
}

dashboard_start('Change Password', 'Update your account password');
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= h($success) ?> <a href="<?= app_url('student/dashboard.php') ?>" class="alert-link">Go to Dashboard</a><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= h($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="data-card" style="max-width:560px;">
    <div class="text-center mb-4">
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;font-size:1.6rem;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
            <i class="fa-solid fa-key"></i>
        </div>
        <h5 class="fw-bold">Update Password</h5>
        <p class="text-muted" style="font-size:0.85rem;">Enter your current password and choose a new one</p>
    </div>

    <form method="POST" novalidate>
        <div class="mb-4">
            <label class="form-label fw-500">Current Password <span class="text-danger">*</span></label>
            <div style="position:relative;">
                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required style="padding-right:44px;">
                <i class="fa-solid fa-lock" onclick="togglePass('current_password',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-500">New Password <span class="text-danger">*</span></label>
            <div style="position:relative;">
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Min 6 characters" required style="padding-right:44px;" oninput="checkStrength(this.value);checkMatch()">
                <i class="fa-solid fa-lock" onclick="togglePass('new_password',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
            </div>
            <div class="mt-2">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Strength:</small>
                    <small id="strengthLabel" class="fw-semibold text-muted">—</small>
                </div>
                <div style="background:#e2e8f0;border-radius:5px;height:5px;">
                    <div id="strengthBar" style="height:5px;border-radius:5px;width:0%;background:#ef4444;transition:width .3s,background .3s;"></div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-500">Confirm New Password <span class="text-danger">*</span></label>
            <div style="position:relative;">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat new password" required style="padding-right:44px;" oninput="checkMatch()">
                <i class="fa-solid fa-lock" onclick="togglePass('confirm_password',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;"></i>
            </div>
            <small id="matchMsg" class="mt-1 d-block"></small>
        </div>

        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 16px;" class="mb-4">
            <p class="mb-1 fw-semibold text-success"><i class="fa-solid fa-shield-halved me-1"></i>Security Tips:</p>
            <ul class="mb-0 small text-success ps-3">
                <li>At least 6 characters long</li>
                <li>Use letters, numbers, and symbols</li>
                <li>Do not share your password</li>
            </ul>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="fa-solid fa-floppy-disk me-2"></i>Update Password
            </button>
            <a href="<?= app_url('student/dashboard.php') ?>" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<script>
function togglePass(id, icon) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'text'
        ? 'fa-solid fa-eye-slash'
        : 'fa-solid fa-lock';
    icon.style.cssText = 'position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;';
}
function checkStrength(val) {
    const bar = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let s = 0;
    if (val.length >= 6) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^a-zA-Z0-9]/.test(val)) s++;
    const levels = [
        {pct:'15%',color:'#ef4444',text:'Very Weak'},
        {pct:'35%',color:'#f97316',text:'Weak'},
        {pct:'60%',color:'#eab308',text:'Fair'},
        {pct:'80%',color:'#22c55e',text:'Strong'},
        {pct:'100%',color:'#16a34a',text:'Very Strong'}
    ];
    const lvl = levels[s] || levels[0];
    bar.style.width = lvl.pct;
    bar.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
}
function checkMatch() {
    const np  = document.getElementById('new_password').value;
    const cp  = document.getElementById('confirm_password').value;
    const msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent = ''; return; }
    msg.innerHTML = np === cp
        ? '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Passwords match</span>'
        : '<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Passwords do not match</span>';
}
</script>

<?php dashboard_end(); ?>