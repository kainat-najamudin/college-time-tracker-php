<?php
require_once __DIR__ . '/includes/layout.php';
start_secure_session();
if (!empty($_SESSION['user_id'])) redirect_to(role_dashboard_path($_SESSION['role']));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $role = login_user($username, $password);
        if ($role) {
            if (!empty($_POST['remember'])) {
                setcookie('ctt_username', $username, time() + 86400 * 30, '/', '', false, true);
            }
            redirect_to(role_dashboard_path($role));
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
page_head('Login', 'auth-body');
?>
<div class="auth-card">
  <div class="auth-header"><div class="auth-logo"><i class="fa-solid fa-clock"></i></div><h3 class="fw-bold mb-1">Welcome Back</h3><p class="mb-0 opacity-75">Sign in to College Time Tracker</p></div>
  <div class="auth-body-card">
    <?php if ($error): ?><div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($error) ?></div><?php endif; ?>
    <form method="POST" id="loginForm">
      <div class="mb-3"><label class="form-label fw-bold">Username</label><div class="input-group"><span class="input-group-text"><i class="fa-solid fa-user"></i></span><input class="form-control" name="username" value="<?= h($_POST['username'] ?? $_COOKIE['ctt_username'] ?? '') ?>" placeholder="Enter username" required></div></div>
      <div class="mb-3"><label class="form-label fw-bold">Password</label><div class="input-group"><span class="input-group-text"><i class="fa-solid fa-lock"></i></span><input type="password" class="form-control" name="password" id="passwordField" placeholder="Enter password" required><button type="button" class="input-group-text" onclick="togglePassword()"><i class="fa-solid fa-eye" id="eyeIcon"></i></button></div></div>
      <div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div><a href="<?= app_url('forgot_password.php') ?>" class="text-decoration-none fw-bold">Forgot?</a></div>
      <button class="btn btn-main w-100" id="loginBtn"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign In</button>
    </form>
    <div class="text-center mt-4"><a href="<?= app_url('signup.php') ?>" class="text-decoration-none fw-bold"><i class="fa-solid fa-user-plus me-1"></i>Create teacher/student account</a></div>
    <div class="text-center mt-3"><a href="<?= app_url('index.php') ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a></div>
      <!-- <div class="alert alert-info mt-4 mb-0 small"><b>Demo:</b> admin/admin123, incharge/123456, sajjad/123456, ayesha/123456</div> -->
   
  </div>
</div>
<?php page_scripts(); ?>
