<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Teacher WHERE TeacherID=$id");
    $success = "Teacher deleted successfully.";
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id          = (int)($_POST['teacher_id'] ?? 0);
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email       = mysqli_real_escape_string($conn, trim($_POST['email']));
    $username    = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password    = trim($_POST['password']);
    $department  = mysqli_real_escape_string($conn, trim($_POST['department']));
    $designation = mysqli_real_escape_string($conn, trim($_POST['designation']));

    if ($id > 0) {
        $sql = "UPDATE Teacher SET Name='$name',Email='$email',Username='$username',Department='$department',Designation='$designation'";
        if (!empty($password)) $sql .= ",Password='".md5($password)."'";
        $sql .= " WHERE TeacherID=$id";
        mysqli_query($conn, $sql) ? $success = "Teacher updated successfully." : $error = mysqli_error($conn);
    } else {
        if (empty($password)) { $error = "Password is required."; }
        else {
            $chk = mysqli_query($conn, "SELECT TeacherID FROM Teacher WHERE Username='$username' OR Email='$email'");
            if (mysqli_num_rows($chk) > 0) {
                $error = "Username or Email already exists.";
            } else {
                mysqli_query($conn, "INSERT INTO Teacher (Name,Email,Username,Password,Department,Designation)
                                     VALUES ('$name','$email','$username','".md5($password)."','$department','$designation')")
                    ? $success = "Teacher added successfully."
                    : $error   = mysqli_error($conn);
            }
        }
    }
}

$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$where  = $search ? "WHERE Name LIKE '%$search%' OR Email LIKE '%$search%' OR Department LIKE '%$search%' OR Designation LIKE '%$search%'" : '';
$list   = mysqli_query($conn, "SELECT * FROM Teacher $where ORDER BY CreatedAt DESC");
$total  = mysqli_num_rows($list);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - CTT Admin</title>
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
                <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Teacher Management
            </div>
            <h4><i class="fas fa-chalkboard-teacher me-2"></i>Teachers</h4>
            <p>Total: <strong><?= $total ?></strong> teacher(s) registered</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#teacherModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Add New Teacher
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-list me-2"></i>All Teachers</div>
            <form method="GET" style="display:flex; gap:0.5rem; align-items:center;">
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search teacher...">
                </div>
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">Search</button>
                <?php if ($search): ?><a href="manage_teachers.php" class="btn btn-sm btn-secondary" style="border-radius:8px;">Clear</a><?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Teacher</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($list) == 0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>No teachers found
                    </td></tr>
                <?php else: $i=1; while ($row = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">
                                    <?= strtoupper(substr($row['Name'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($row['Name']) ?></div>
                                    <div style="font-size:0.75rem;color:#9ca3af;"><?= htmlspecialchars($row['Designation'] ?: 'Faculty') ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td><code><?= htmlspecialchars($row['Username']) ?></code></td>
                        <td><?= htmlspecialchars($row['Department'] ?: '—') ?></td>
                        <td>
                            <?php if ($row['Designation']): ?>
                                <span style="background:#f5f3ff;color:#7c3aed;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">
                                    <?= htmlspecialchars($row['Designation']) ?>
                                </span>
                            <?php else: echo '—'; endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['CreatedAt'])) ?></td>
                        <td>
                            <button class="btn-action btn-edit" title="Edit"
                                onclick="editTeacher(<?= htmlspecialchars(json_encode($row)) ?>)"
                                data-bs-toggle="modal" data-bs-target="#teacherModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $row['TeacherID'] ?>" class="btn-action btn-delete ms-1"
                               onclick="return confirm('Delete this teacher?')" title="Delete">
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
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-chalkboard-teacher me-2"></i>Add Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="teacher_id" id="teacherId" value="0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="f_name" placeholder="Full name" required>
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
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="f_password" placeholder="Password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" id="f_department" placeholder="Department">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Designation</label>
                            <select class="form-select" name="designation" id="f_designation">
                                <option value="">-- Select --</option>
                                <option value="Lecturer">Lecturer</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                                <option value="Lab Instructor">Lab Instructor</option>
                                <option value="Visiting Faculty">Visiting Faculty</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetForm() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-chalkboard-teacher me-2"></i>Add Teacher';
    document.getElementById('teacherId').value = '0';
    ['f_name','f_email','f_username','f_password','f_department'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f_designation').value = '';
}
function editTeacher(d) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Teacher';
    document.getElementById('teacherId').value    = d.TeacherID;
    document.getElementById('f_name').value       = d.Name;
    document.getElementById('f_email').value      = d.Email;
    document.getElementById('f_username').value   = d.Username;
    document.getElementById('f_password').value   = '';
    document.getElementById('f_department').value = d.Department || '';
    document.getElementById('f_designation').value= d.Designation || '';
    document.getElementById('f_password').placeholder = 'Leave blank to keep current';
}
</script>
</body>
</html>
