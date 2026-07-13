<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Student WHERE StudentID=$id");
    $success = "Student deleted successfully.";
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id         = (int)($_POST['student_id'] ?? 0);
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $rollno     = mysqli_real_escape_string($conn, trim($_POST['rollno']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $username   = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password   = trim($_POST['password']);
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $semester   = mysqli_real_escape_string($conn, trim($_POST['semester']));

    if ($id > 0) {
        $sql = "UPDATE Student SET Name='$name',RollNo='$rollno',Email='$email',Username='$username',Department='$department',Semester='$semester'";
        if (!empty($password)) $sql .= ",Password='".md5($password)."'";
        $sql .= " WHERE StudentID=$id";
        mysqli_query($conn, $sql) ? $success = "Student updated successfully." : $error = mysqli_error($conn);
    } else {
        if (empty($password)) { $error = "Password is required."; }
        else {
            $chk = mysqli_query($conn, "SELECT StudentID FROM Student WHERE Username='$username' OR Email='$email' OR RollNo='$rollno'");
            if (mysqli_num_rows($chk) > 0) {
                $error = "Username, Email, or Roll No already exists.";
            } else {
                mysqli_query($conn, "INSERT INTO Student (Name,RollNo,Email,Username,Password,Department,Semester)
                                     VALUES ('$name','$rollno','$email','$username','".md5($password)."','$department','$semester')")
                    ? $success = "Student added successfully."
                    : $error   = mysqli_error($conn);
            }
        }
    }
}

// Filter
$search   = isset($_GET['q'])        ? mysqli_real_escape_string($conn, $_GET['q'])        : '';
$f_dept   = isset($_GET['dept'])     ? mysqli_real_escape_string($conn, $_GET['dept'])     : '';
$f_sem    = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : '';

$where = "WHERE 1=1";
if ($search) $where .= " AND (Name LIKE '%$search%' OR Email LIKE '%$search%' OR RollNo LIKE '%$search%')";
if ($f_dept) $where .= " AND Department='$f_dept'";
if ($f_sem)  $where .= " AND Semester='$f_sem'";

$list  = mysqli_query($conn, "SELECT * FROM Student $where ORDER BY CreatedAt DESC");
$total = mysqli_num_rows($list);

// Dept & Semester lists for filter
$depts    = mysqli_query($conn, "SELECT DISTINCT Department FROM Student WHERE Department != '' ORDER BY Department");
$semesters_list = ['1st Semester','2nd Semester','3rd Semester','4th Semester','5th Semester','6th Semester','7th Semester','8th Semester'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - CTT Admin</title>
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
                <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Student Management
            </div>
            <h4><i class="fas fa-user-graduate me-2"></i>Students</h4>
            <p>Total: <strong><?= $total ?></strong> student(s) enrolled</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="resetStudentForm()">
            <i class="fas fa-plus me-2"></i>Add New Student
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

    <!-- Filter Bar -->
    <div class="form-card mb-4" style="padding:1.2rem 1.5rem;">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <div class="table-search" style="background:#f8faff;">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email, roll no...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select class="form-select" name="dept">
                    <option value="">All Departments</option>
                    <?php while($d = mysqli_fetch_row($depts)): ?>
                        <option value="<?= $d[0] ?>" <?= $f_dept==$d[0]?'selected':'' ?>><?= htmlspecialchars($d[0]) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Semester</label>
                <select class="form-select" name="semester">
                    <option value="">All Semesters</option>
                    <?php foreach($semesters_list as $s): ?>
                        <option value="<?=$s?>" <?=$f_sem==$s?'selected':''?>><?=$s?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn-primary-custom w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="manage_students.php" class="btn btn-light w-100" style="border-radius:10px;">Clear</a>
            </div>
        </form>
    </div>

    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-list me-2"></i>Student List</div>
            <span style="font-size:0.85rem; color:#64748b;"><?= $total ?> result(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Roll No</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($total == 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>No students found
                    </td></tr>
                <?php else: $i=1; while ($row = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">
                                    <?= strtoupper(substr($row['Name'],0,1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($row['Name']) ?></div>
                                    <div style="font-size:0.75rem;color:#9ca3af;"><code><?= htmlspecialchars($row['Username']) ?></code></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="background:#fff7ed;color:#ea580c;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">
                                <?= htmlspecialchars($row['RollNo']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td><?= htmlspecialchars($row['Department'] ?: '—') ?></td>
                        <td>
                            <?php if ($row['Semester']): ?>
                                <span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">
                                    <?= htmlspecialchars($row['Semester']) ?>
                                </span>
                            <?php else: echo '—'; endif; ?>
                        </td>
                        <td>
                            <button class="btn-action btn-edit" title="Edit"
                                onclick="editStudent(<?= htmlspecialchars(json_encode($row)) ?>)"
                                data-bs-toggle="modal" data-bs-target="#studentModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $row['StudentID'] ?>" class="btn-action btn-delete ms-1"
                               onclick="return confirm('Delete this student?')" title="Delete">
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
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stuModalTitle"><i class="fas fa-user-graduate me-2"></i>Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="student_id" id="stuId" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="s_name" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Roll Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="rollno" id="s_rollno" placeholder="e.g. BSCS-F21-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="s_email" placeholder="email@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="s_username" placeholder="Username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="s_password" placeholder="Password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" id="s_department" placeholder="e.g. Computer Science">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" id="s_semester">
                                <option value="">-- Select Semester --</option>
                                <?php foreach($semesters_list as $s): ?>
                                    <option value="<?=$s?>"><?=$s?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetStudentForm() {
    document.getElementById('stuModalTitle').innerHTML = '<i class="fas fa-user-graduate me-2"></i>Add Student';
    document.getElementById('stuId').value = '0';
    ['s_name','s_rollno','s_email','s_username','s_password','s_department'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('s_semester').value = '';
}
function editStudent(d) {
    document.getElementById('stuModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Student';
    document.getElementById('stuId').value        = d.StudentID;
    document.getElementById('s_name').value       = d.Name;
    document.getElementById('s_rollno').value     = d.RollNo;
    document.getElementById('s_email').value      = d.Email;
    document.getElementById('s_username').value   = d.Username;
    document.getElementById('s_password').value   = '';
    document.getElementById('s_department').value = d.Department || '';
    document.getElementById('s_semester').value   = d.Semester   || '';
    document.getElementById('s_password').placeholder = 'Leave blank to keep current';
}
</script>
</body>
</html>
