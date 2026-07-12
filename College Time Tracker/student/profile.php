<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('student');

$uid  = (int)current_user_id();
$user = fetch_all("SELECT * FROM student WHERE StudentID=$uid")[0] ?? [];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $semester   = trim($_POST['semester'] ?? '');

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        $name_s       = mysqli_real_escape_string($conn, $name);
        $email_s      = mysqli_real_escape_string($conn, $email);
        $department_s = mysqli_real_escape_string($conn, $department);
        $semester_s   = mysqli_real_escape_string($conn, $semester);
        if (mysqli_query($conn, "UPDATE student SET Name='$name_s', Email='$email_s', Department='$department_s', Semester='$semester_s' WHERE StudentID=$uid")) {
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully.";
            $user = fetch_all("SELECT * FROM student WHERE StudentID=$uid")[0] ?? [];
        } else {
            $error = mysqli_error($conn);
        }
    }
}

dashboard_start('My Profile', 'View and edit your profile');
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= h($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= h($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="data-card text-center">
            <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-size:2.2rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <?= strtoupper(substr($user['Name'] ?? 'S', 0, 1)) ?>
            </div>
            <h5 class="fw-bold"><?= h($user['Name'] ?? '') ?></h5>
            <p class="text-muted" style="font-size:0.85rem;"><?= h($user['Email'] ?? '') ?></p>
            <span style="background:#ecfeff;color:#0891b2;padding:5px 16px;border-radius:50px;font-size:0.82rem;font-weight:700;">
                <i class="fa-solid fa-user-graduate me-1"></i>Student
            </span>
            <div class="mt-3 p-3 text-start bg-light rounded">
                <small class="text-muted">Roll No</small>
                <div class="fw-bold"><?= h($user['RollNo'] ?? '') ?></div>
            </div>
            <div class="mt-2 p-3 text-start bg-light rounded">
                <small class="text-muted">Department</small>
                <div class="fw-bold"><?= h($user['Department'] ?? 'Not Set') ?></div>
            </div>
            <div class="mt-2 p-3 text-start bg-light rounded">
                <small class="text-muted">Semester</small>
                <div class="fw-bold"><?= h($user['Semester'] ?? 'Not Set') ?></div>
            </div>
            <div class="mt-2 p-3 text-start bg-light rounded">
                <small class="text-muted">Username</small>
                <div class="fw-bold"><?= h($user['Username'] ?? '') ?></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="data-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Profile</h5>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-500">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= h($user['Name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= h($user['Email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Department</label>
                        <input type="text" name="department" class="form-control" value="<?= h($user['Department'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Semester</label>
                        <input type="text" name="semester" class="form-control" value="<?= h($user['Semester'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Roll No</label>
                        <input type="text" class="form-control bg-light" value="<?= h($user['RollNo'] ?? '') ?>" disabled>
                        <div class="form-text">Roll No cannot be changed.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Username</label>
                        <input type="text" class="form-control bg-light" value="<?= h($user['Username'] ?? '') ?>" disabled>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php dashboard_end(); ?>