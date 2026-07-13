<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');

$uid  = (int)current_user_id();
$user = fetch_all("SELECT * FROM administrator WHERE AdminID = $uid")[0] ?? [];

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        global $conn;
        $name_s  = mysqli_real_escape_string($conn, $name);
        $email_s = mysqli_real_escape_string($conn, $email);
        if (mysqli_query($conn, "UPDATE administrator SET Name='$name_s', Email='$email_s' WHERE AdminID=$uid")) {
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully.";
            $user = fetch_all("SELECT * FROM administrator WHERE AdminID = $uid")[0] ?? [];
        } else {
            $error = mysqli_error($conn);
        }
    }
}

dashboard_start('Admin Profile', 'View and edit your profile');
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
            <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;font-size:2rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:auto;">
                <?= strtoupper(substr($user['Name'] ?? 'A', 0, 1)) ?>
            </div>
            <h5 class="mt-3 fw-bold"><?= h($user['Name'] ?? '') ?></h5>
            <p class="text-muted"><?= h($user['Email'] ?? '') ?></p>
            <span class="badge bg-danger px-3 py-2">Administrator</span>
            <div class="mt-3 text-start p-3 bg-light rounded">
                <small class="text-muted">Username</small>
                <div class="fw-bold"><?= h($user['Username'] ?? '') ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="data-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Profile</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-500">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= h($user['Name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= h($user['Email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Username</label>
                    <input type="text" class="form-control" value="<?= h($user['Username'] ?? '') ?>" disabled>
                </div>
                <button type="submit" class="btn btn-danger px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php dashboard_end(); ?>