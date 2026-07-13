<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$uid  = (int)$_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM administrator WHERE AdminID = $uid"));

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {

        $sql = "UPDATE administrator 
                SET Name='$name', Email='$email'
                WHERE AdminID = $uid";

        if (mysqli_query($conn, $sql)) {
            $success = "Profile updated successfully.";
            $_SESSION['user_name'] = $name;

            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM administrator WHERE AdminID = $uid"));
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
<title>Admin Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content">

<div class="page-header">
    <h4><i class="fas fa-user-shield me-2"></i>Admin Profile</h4>
</div>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="form-card text-center">

            <div style="width:90px;height:90px;border-radius:50%;
                background:linear-gradient(135deg,#dc2626,#ef4444);
                color:#fff;font-size:2rem;font-weight:900;
                display:flex;align-items:center;justify-content:center;margin:auto;">
                <?= strtoupper(substr($user['Name'], 0, 1)) ?>
            </div>

            <h5 class="mt-3"><?= htmlspecialchars($user['Name']) ?></h5>
            <p><?= htmlspecialchars($user['Email']) ?></p>

            <span class="badge bg-danger">Administrator</span>

            <div class="mt-3 text-start p-3 bg-light rounded">
                <small>Username</small>
                <div class="fw-bold"><?= htmlspecialchars($user['Username']) ?></div>
            </div>

        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-lg-8">
        <div class="form-card">

            <h5>Edit Profile</h5>

            <form method="POST">

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($user['Name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($user['Email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($user['Username']) ?>" disabled>
                </div>

                <button class="btn btn-danger">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>

            </form>

        </div>
    </div>

</div>

</main>

</body>
</html>