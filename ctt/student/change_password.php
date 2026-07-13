<?php
// student/change_password.php
define('BASE_URL', '../');
require_once BASE_URL . 'includes/db.php';
require_once BASE_URL . 'includes/session.php';
requireStudent();

$student_id = $_SESSION['student_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT Password FROM student WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    $row = $stmt->fetch();

    if (md5($current) !== $row['Password']) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_pass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_pass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $upd = $pdo->prepare("UPDATE student SET Password=? WHERE StudentID=?");
        $upd->execute([md5($new_pass), $student_id]);
        $success = 'Password changed successfully!';
    }
}

$page_title = "Change Password";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CTT System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout.css">
    <style>
        .page-header { background:linear-gradient(135deg,#11998e,#38ef7d); color:#fff; border-radius:16px; padding:24px 28px; margin-bottom:24px; }
        .page-header h4 { font-weight:700; margin:0; }
        .pass-card { border:none; border-radius:16px; box-shadow:0 2px 20px rgba(0,0,0,.09); max-width:560px; }
        .form-label { font-weight:600; color:#374151; font-size:.88rem; }
        .form-control { border-radius:10px; padding:10px 14px; border:1.5px solid #e5e7eb; }
        .form-control:focus { border-color:#11998e; box-shadow:0 0 0 3px rgba(17,153,142,.15); }
        .input-group-text { border-radius:10px 0 0 10px !important; background:#f8fafc; cursor:pointer; }
        .form-control.input-right { border-radius:0 10px 10px 0 !important; }
        .tip-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:14px 16px; }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include BASE_URL . 'includes/sidebar_student.php'; ?>
    <div class="main-content">
        <?php include BASE_URL . 'includes/header.php'; ?>
        <div class="content-area">
            <div class="container-fluid">

                <div class="page-header">
                    <h4><i class="fas fa-key me-2"></i>Change Password</h4>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div><strong>Success!</strong> <?php echo $success; ?> <a href="dashboard.php" class="alert-link">Go to Dashboard</a></div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div><?php echo $error; ?></div>
                </div>
                <?php endif; ?>

                <div class="card pass-card p-4">
                    <form method="POST" novalidate>
                        <div class="mb-4">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" onclick="togglePass('current_password','icon_cur')">
                                    <i class="fas fa-lock text-muted" id="icon_cur"></i>
                                </span>
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control input-right" placeholder="Enter current password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" onclick="togglePass('new_password','icon_new')">
                                    <i class="fas fa-lock text-muted" id="icon_new"></i>
                                </span>
                                <input type="password" id="new_password" name="new_password"
                                       class="form-control input-right" placeholder="Min 6 characters" required
                                       oninput="checkStrength(this.value)">
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
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" onclick="togglePass('confirm_password','icon_con')">
                                    <i class="fas fa-lock text-muted" id="icon_con"></i>
                                </span>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       class="form-control input-right" placeholder="Repeat new password" required
                                       oninput="checkMatch()">
                            </div>
                            <small id="matchMsg" class="mt-1 d-block"></small>
                        </div>

                        <div class="tip-box mb-4">
                            <p class="mb-1 fw-semibold text-success"><i class="fas fa-shield-alt me-1"></i>Security Tips:</p>
                            <ul class="mb-0 small text-success ps-3">
                                <li>At least 6 characters long</li>
                                <li>Use letters, numbers, and symbols</li>
                                <li>Do not share your password</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-success px-4" style="border-radius:10px;">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary px-4" style="border-radius:10px;">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/layout.js"></script>
<script>
function togglePass(id, iconId) {
    const input = document.getElementById(id);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'text' ? 'fas fa-eye-slash text-muted' : 'fas fa-lock text-muted';
}

function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let s = 0;
    if (val.length >= 6)  s++;
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
        ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</span>'
        : '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords do not match</span>';
}
</script>
</body>
</html>
