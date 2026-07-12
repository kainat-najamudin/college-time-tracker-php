<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');

$uid = (int)current_user_id();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $old     = $_POST['old_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $user = fetch_all("SELECT Password FROM administrator WHERE AdminID = $uid")[0] ?? [];

    if (!password_matches($old, $user['Password'] ?? '')) {
        $error = "Old password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_for_storage($new);
        $hashed_s = mysqli_real_escape_string($conn, $hashed);
        if (mysqli_query($conn, "UPDATE administrator SET Password='$hashed_s' WHERE AdminID=$uid")) {
            $success = "Password changed successfully.";
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

<div class="data-card" style="max-width:600px;">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-key me-2 text-primary"></i>Change Password</h5>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-500">Old Password</label>
            <input type="password" name="old_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-500">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-500">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger px-4">
            <i class="fa-solid fa-lock me-2"></i>Update Password
        </button>
    </form>
</div>

<?php dashboard_end(); ?>