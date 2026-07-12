<?php
require_once __DIR__ . '/includes/layout.php';
$message='';$type='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email'] ?? '');$role=trim($_POST['role'] ?? '');
    $map=['admin'=>'administrator','incharge'=>'academicsincharge','teacher'=>'teacher','student'=>'student'];
    if (!$email || !$role || !isset($map[$role])) {$message='Please enter valid email and role.';$type='danger';}
    else {$stmt=mysqli_prepare($conn,"SELECT Name FROM `{$map[$role]}` WHERE Email=? LIMIT 1");mysqli_stmt_bind_param($stmt,'s',$email);mysqli_stmt_execute($stmt);$res=mysqli_stmt_get_result($stmt);$user=mysqli_fetch_assoc($res);mysqli_stmt_close($stmt);if($user){$message='Password reset instructions have been generated for '.h($email).'. In real hosting, connect PHPMailer/SMTP to send reset link.';$type='success';}else{$message='No account found with this email for selected role.';$type='danger';}}
}
page_head('Forgot Password','auth-body');
?>
<div class="auth-card">
  <div class="auth-header"><div class="auth-logo"><i class="fa-solid fa-key"></i></div><h3 class="fw-bold mb-1">Forgot Password?</h3><p class="mb-0 opacity-75">Recover your CTT account</p></div>
  <div class="auth-body-card">
    <?php if($message): ?><div class="alert alert-<?= h($type) ?>"><?= $message ?></div><?php endif; ?>
    <div class="alert alert-info small"><i class="fa-solid fa-circle-info me-2"></i>For final-year demo, this page validates email and role. Email sending can be integrated using SMTP.</div>
    <form method="POST"><div class="mb-3"><label class="form-label fw-bold">Email Address</label><input type="email" name="email" class="form-control" value="<?= h($_POST['email'] ?? '') ?>" required></div><div class="mb-4"><label class="form-label fw-bold">Role</label><select name="role" class="form-select" required><option value="">Select Role</option><?php foreach(['admin'=>'Administrator','incharge'=>'Academic In-Charge','teacher'=>'Teacher','student'=>'Student'] as $k=>$v): ?><option value="<?= $k ?>" <?= (($_POST['role'] ?? '')===$k)?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div><button class="btn btn-main w-100"><i class="fa-solid fa-paper-plane me-2"></i>Send Reset Instructions</button></form>
    <div class="text-center mt-3"><a href="<?= app_url('login.php') ?>" class="fw-bold text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Back to Login</a></div>
  </div>
</div>
<?php page_scripts(); ?>
