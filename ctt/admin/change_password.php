<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$uid = (int)$_SESSION['user_id'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $user = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT Password FROM administrator WHERE AdminID = $uid"
    ));

    if (!password_verify($old, $user['Password'])) {
        $error = "Old password is incorrect.";
    }
    elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    }
    elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $sql = "UPDATE administrator SET Password='$hashed' WHERE AdminID=$uid";

        if (mysqli_query($conn, $sql)) {
            $success = "Password changed successfully.";
        } else {
            $error = mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content">

<div class="page-header">
    <h4><i class="fas fa-key me-2"></i>Change Password</h4>
</div>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="form-card" style="max-width:600px;">

<form method="POST">

    <div class="mb-3">
        <label>Old Password</label>
        <input type="password" name="old_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <button class="btn btn-danger">
        <i class="fas fa-lock me-2"></i>Update Password
    </button>

</form>

</div>

</main>

</body>
</html>