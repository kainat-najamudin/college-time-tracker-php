<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM AcademicsIncharge WHERE InchargeID=$id");
    $success = "Academic In-Charge deleted successfully.";
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id         = (int)($_POST['incharge_id'] ?? 0);
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $username   = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password   = trim($_POST['password']);
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));

    if ($id > 0) {
        // EDIT
        $sql = "UPDATE AcademicsIncharge SET Name='$name', Email='$email', Username='$username', Department='$department'";
        if (!empty($password)) $sql .= ", Password='" . md5($password) . "'";
        $sql .= " WHERE InchargeID=$id";
        mysqli_query($conn, $sql) ? $success = "In-Charge updated successfully." : $error = mysqli_error($conn);
    } else {
        // ADD
        if (empty($password)) { $error = "Password is required."; }
        else {
            $chk = mysqli_query($conn, "SELECT InchargeID FROM AcademicsIncharge WHERE Username='$username' OR Email='$email'");
            if (mysqli_num_rows($chk) > 0) {
                $error = "Username or Email already exists.";
            } else {
                $admin_id = $_SESSION['user_id'];
                mysqli_query($conn, "INSERT INTO AcademicsIncharge (Name,Email,Username,Password,Department,CreatedBy)
                                     VALUES ('$name','$email','$username','".md5($password)."','$department',$admin_id)")
                    ? $success = "Academic In-Charge added successfully."
                    : $error   = mysqli_error($conn);
            }
        }
    }
}

// Fetch edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id   = (int)$_GET['edit'];
    $res       = mysqli_query($conn, "SELECT * FROM AcademicsIncharge WHERE InchargeID=$edit_id");
    $edit_data = mysqli_fetch_assoc($res);
}

// Fetch all
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$where  = $search ? "WHERE Name LIKE '%$search%' OR Email LIKE '%$search%' OR Department LIKE '%$search%'" : '';
$list   = mysqli_query($conn, "SELECT * FROM AcademicsIncharge $where ORDER BY CreatedAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage In-Charge - CTT Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom">
                <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> In-Charge Management
            </div>
            <h4><i class="fas fa-user-tie me-2"></i>Academic In-Charge</h4>
            <p>Manage all Academic In-Charge accounts</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#formModal">
            <i class="fas fa-plus me-2"></i>Add New In-Charge
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-list me-2"></i>All In-Charge Accounts</div>
            <form method="GET" style="display:flex; gap:0.5rem; align-items:center;">
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, dept...">
                </div>
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">Search</button>
                <?php if ($search): ?><a href="manage_incharge.php" class="btn btn-sm btn-secondary" style="border-radius:8px;">Clear</a><?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table" id="inchargeTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Department</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($list) == 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>No In-Charge found
                    </td></tr>
                <?php else: $i=1; while ($row = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#065f46,#059669);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">
                                    <?= strtoupper(substr($row['Name'],0,1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($row['Name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td><code><?= htmlspecialchars($row['Username']) ?></code></td>
                        <td><?= htmlspecialchars($row['Department'] ?: '—') ?></td>
                        <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                        <td>
                            <a href="?edit=<?= $row['InchargeID'] ?>" class="btn-action btn-edit" title="Edit"
                               data-bs-toggle="modal" data-bs-target="#formModal"
                               onclick="fillEdit(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?= $row['InchargeID'] ?>" class="btn-action btn-delete ms-1"
                               onclick="return confirm('Delete this In-Charge account?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-user-tie me-2"></i>Add Academic In-Charge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="incharge_id" id="inchargeId" value="0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="f_name" placeholder="Enter full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="f_email" placeholder="email@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="f_username" placeholder="Username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger" id="passRequired">*</span></label>
                            <input type="password" class="form-control" name="password" id="f_password" placeholder="Leave blank to keep current">
                            <div style="font-size:0.78rem;color:#9ca3af;margin-top:4px;" id="passHint"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" id="f_department" placeholder="e.g. Computer Science">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Save In-Charge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function fillEdit(data) {
    document.getElementById('modalTitle').innerHTML   = '<i class="fas fa-edit me-2"></i>Edit Academic In-Charge';
    document.getElementById('inchargeId').value       = data.InchargeID;
    document.getElementById('f_name').value           = data.Name;
    document.getElementById('f_email').value          = data.Email;
    document.getElementById('f_username').value       = data.Username;
    document.getElementById('f_department').value     = data.Department || '';
    document.getElementById('f_password').placeholder = 'Leave blank to keep current password';
    document.getElementById('passHint').textContent   = 'Leave blank to keep existing password.';
    document.getElementById('passRequired').style.display = 'none';
}
document.querySelector('[data-bs-target="#formModal"]').addEventListener('click', function() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-tie me-2"></i>Add Academic In-Charge';
    document.getElementById('inchargeId').value     = '0';
    document.getElementById('f_name').value         = '';
    document.getElementById('f_email').value        = '';
    document.getElementById('f_username').value     = '';
    document.getElementById('f_password').value     = '';
    document.getElementById('f_department').value   = '';
    document.getElementById('f_password').placeholder = 'Enter password';
    document.getElementById('passHint').textContent   = '';
    document.getElementById('passRequired').style.display = 'inline';
});
</script>
</body>
</html>
