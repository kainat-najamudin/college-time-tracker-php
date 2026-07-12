<?php
require_once __DIR__ . '/includes/layout.php';
start_secure_session();
if (!empty($_SESSION['user_id'])) redirect_to(role_dashboard_path($_SESSION['role']));
$errors = [];$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $rollno = trim($_POST['rollno'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    if (!in_array($role, ['teacher','student'], true)) $errors[] = 'Select Teacher or Student role.';
    if ($name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($username) < 4) $errors[] = 'Username must be at least 4 characters.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($_POST['terms'])) $errors[] = 'You must accept terms and conditions.';
    if ($role === 'student' && $rollno === '') $errors[] = 'Roll number is required for students.';
    if (!$errors) {
        foreach (['administrator','academicsincharge','teacher','student'] as $table) {
            $stmt = mysqli_prepare($conn, "SELECT 1 FROM `$table` WHERE Username=? OR Email=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) $errors[] = 'Username or email already exists.';
            mysqli_stmt_close($stmt);
            if ($errors) break;
        }
    }
    if (!$errors && $role === 'student') {
        $stmt = mysqli_prepare($conn, 'SELECT 1 FROM student WHERE RollNo=? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $rollno);
        mysqli_stmt_execute($stmt);mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) $errors[] = 'Roll number already exists.';
        mysqli_stmt_close($stmt);
    }
    if (!$errors) {
        $hash = password_for_storage($password);
        if ($role === 'teacher') {
            $stmt = mysqli_prepare($conn, 'INSERT INTO teacher (Name,Email,Username,Password,Department,Designation) VALUES (?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'ssssss', $name,$email,$username,$hash,$department,$designation);
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO student (Name,RollNo,Email,Username,Password,Department,Semester) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'sssssss', $name,$rollno,$email,$username,$hash,$department,$semester);
        }
        if (mysqli_stmt_execute($stmt)) $success = true; else $errors[] = 'Registration failed: ' . mysqli_error($conn);
        mysqli_stmt_close($stmt);
    }
}
page_head('Sign Up', 'auth-body');
?>
<div class="auth-card" style="max-width:760px;">
  <div class="auth-header"><div class="auth-logo"><i class="fa-solid fa-user-plus"></i></div><h3 class="fw-bold mb-1">Create Account</h3><p class="mb-0 opacity-75">Teacher and Student Registration Portal</p></div>
  <div class="auth-body-card">
    <?php if ($success): ?>
      <div class="text-center py-4"><div class="display-3 text-success"><i class="fa-solid fa-circle-check"></i></div><h3 class="fw-bold text-success mt-3">Registration Successful!</h3><p class="text-muted">Your account has been created. You can now login.</p><a class="btn btn-main px-5" href="<?= app_url('login.php') ?>">Proceed to Login</a></div>
    <?php else: ?>
      <?php if ($errors): ?><div class="alert alert-danger"><b>Please fix:</b><ul class="mb-0 mt-2"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="POST" id="signupForm">
        <input type="hidden" name="role" id="roleInput" value="<?= h($_POST['role'] ?? '') ?>">
        <label class="form-label fw-bold">Select Role</label><div class="role-picker mb-3"><div class="role-choice <?= (($_POST['role'] ?? '')==='teacher')?'active':'' ?>" onclick="selectSignupRole('teacher',this)"><i class="fa-solid fa-chalkboard-teacher fa-2x mb-2"></i><div class="fw-bold">Teacher</div><small>Instructor / Faculty</small></div><div class="role-choice <?= (($_POST['role'] ?? '')==='student')?'active':'' ?>" onclick="selectSignupRole('student',this)"><i class="fa-solid fa-user-graduate fa-2x mb-2"></i><div class="fw-bold">Student</div><small>Enrolled Student</small></div></div>
        <div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold">Full Name</label><input class="form-control" name="name" value="<?= h($_POST['name'] ?? '') ?>" required></div><div class="col-md-6"><label class="form-label fw-bold">Email</label><input type="email" class="form-control" name="email" value="<?= h($_POST['email'] ?? '') ?>" required></div><div class="col-md-6"><label class="form-label fw-bold">Department</label><input class="form-control" name="department" value="<?= h($_POST['department'] ?? '') ?>"></div><div class="col-md-6 teacher-field <?= (($_POST['role'] ?? '')==='teacher')?'':'d-none' ?>"><label class="form-label fw-bold">Designation</label><input class="form-control" name="designation" value="<?= h($_POST['designation'] ?? '') ?>"></div><div class="col-md-6 student-field <?= (($_POST['role'] ?? '')==='student')?'':'d-none' ?>"><label class="form-label fw-bold">Roll No</label><input class="form-control" name="rollno" value="<?= h($_POST['rollno'] ?? '') ?>"></div><div class="col-md-6 student-field <?= (($_POST['role'] ?? '')==='student')?'':'d-none' ?>"><label class="form-label fw-bold">Semester</label><select class="form-select" name="semester"><option value="">Select Semester</option><?php foreach(['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?><option <?= (($_POST['semester'] ?? '')===$s)?'selected':'' ?> value="<?= $s ?>"><?= $s ?> Semester</option><?php endforeach; ?></select></div><div class="col-md-12"><label class="form-label fw-bold">Username</label><input class="form-control" name="username" value="<?= h($_POST['username'] ?? '') ?>" minlength="4" required></div><div class="col-md-6"><label class="form-label fw-bold">Password</label><input type="password" class="form-control" name="password" minlength="6" required></div><div class="col-md-6"><label class="form-label fw-bold">Confirm Password</label><input type="password" class="form-control" name="confirm_password" minlength="6" required></div></div>
        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="terms" id="terms" <?= isset($_POST['terms'])?'checked':'' ?>><label class="form-check-label" for="terms">I accept Terms & Conditions.</label></div><button class="btn btn-main w-100 mt-4" id="signupBtn"><i class="fa-solid fa-user-plus me-2"></i>Create Account</button><div class="text-center mt-3"><a href="<?= app_url('login.php') ?>" class="fw-bold text-decoration-none">Already have account? Login</a></div>
      </form>
    <?php endif; ?>
    <div class="text-center mt-3"><a href="<?= app_url('index.php') ?>" class="text-muted text-decoration-none"><i class="fa-solid fa-home me-1"></i>Back to Home</a></div>
  </div>
</div>
<?php page_scripts(); ?>
