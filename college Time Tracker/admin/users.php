<?php
require_once __DIR__ . '/../includes/layout.php';
require_any_role(['admin','incharge']);
$roleNow = current_role();
$canManageIncharge = $roleNow === 'admin';

// ── DELETE ──────────────────────────────────────────────────────────────
if (isset($_GET['delete'], $_GET['type'])) {
    $type = $_GET['type']; $id = (int)$_GET['delete'];
    $map = [
        'incharge' => ['academicsincharge', 'InchargeID'],
        'teacher'  => ['teacher',           'TeacherID'],
        'student'  => ['student',           'StudentID'],
    ];
    if (isset($map[$type]) && ($type !== 'incharge' || $canManageIncharge)) {
        mysqli_query($conn, "DELETE FROM `{$map[$type][0]}` WHERE {$map[$type][1]}=$id");
        add_flash('success', 'Account deleted successfully.');
    }
    redirect_to($roleNow === 'admin' ? 'admin/users.php' : 'incharge/users.php');
}

// ── UPDATE ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $type   = $_POST['type']   ?? '';
    $id     = (int)($_POST['record_id'] ?? 0);
    $name   = trim($_POST['name']        ?? '');
    $email  = trim($_POST['email']       ?? '');
    $username = trim($_POST['username']  ?? '');
    $department  = trim($_POST['department']  ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $rollno      = trim($_POST['rollno']      ?? '');
    $semester    = trim($_POST['semester']    ?? '');
    $rawpw  = trim($_POST['password'] ?? '');
    $stmt   = null;

    if (!$name || !$email || !$username) {
        add_flash('error', 'Name, email and username are required.');
    } elseif ($type === 'incharge' && !$canManageIncharge) {
        add_flash('error', 'Only admin can edit Academic In-Charge.');
    } else {
        // Build SET clause — only update password if provided
        if ($type === 'incharge') {
            if ($rawpw) {
                $h = password_for_storage($rawpw);
                $stmt = mysqli_prepare($conn, 'UPDATE academicsincharge SET Name=?,Email=?,Username=?,Password=?,Department=? WHERE InchargeID=?');
                mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $username, $h, $department, $id);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE academicsincharge SET Name=?,Email=?,Username=?,Department=? WHERE InchargeID=?');
                mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $username, $department, $id);
            }
        } elseif ($type === 'teacher') {
            if ($rawpw) {
                $h = password_for_storage($rawpw);
                $stmt = mysqli_prepare($conn, 'UPDATE teacher SET Name=?,Email=?,Username=?,Password=?,Department=?,Designation=? WHERE TeacherID=?');
                mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $email, $username, $h, $department, $designation, $id);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE teacher SET Name=?,Email=?,Username=?,Department=?,Designation=? WHERE TeacherID=?');
                mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $username, $department, $designation, $id);
            }
        } elseif ($type === 'student') {
            if ($rawpw) {
                $h = password_for_storage($rawpw);
                $stmt = mysqli_prepare($conn, 'UPDATE student SET Name=?,RollNo=?,Email=?,Username=?,Password=?,Department=?,Semester=? WHERE StudentID=?');
                mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $rollno, $email, $username, $h, $department, $semester, $id);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE student SET Name=?,RollNo=?,Email=?,Username=?,Department=?,Semester=? WHERE StudentID=?');
                mysqli_stmt_bind_param($stmt, 'sssssi', $name, $rollno, $email, $username, $department, $semester, $id);
            }
        }
        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) add_flash('success', 'Account updated successfully.');
            else                            add_flash('error',   'Failed: ' . mysqli_error($conn));
            mysqli_stmt_close($stmt);
        }
    }
    redirect_to($roleNow === 'admin' ? 'admin/users.php' : 'incharge/users.php');
}

// ── CREATE ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type        = $_POST['type']        ?? '';
    $name        = trim($_POST['name']        ?? '');
    $email       = trim($_POST['email']       ?? '');
    $username    = trim($_POST['username']    ?? '');
    $password    = trim($_POST['password']    ?? '123456');
    $department  = trim($_POST['department']  ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $rollno      = trim($_POST['rollno']      ?? '');
    $semester    = trim($_POST['semester']    ?? '');

    if ($type === 'incharge' && !$canManageIncharge) {
        add_flash('error', 'Only admin can create Academic In-Charge.');
    } elseif (!$name || !$email || !$username) {
        add_flash('error', 'Name, email and username are required.');
    } else {
        $hash = password_for_storage($password);
        if ($type === 'incharge') {
            $created = (int)current_user_id();
            $stmt = mysqli_prepare($conn, 'INSERT INTO academicsincharge (Name,Email,Username,Password,Department,CreatedBy) VALUES (?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $username, $hash, $department, $created);
        } elseif ($type === 'teacher') {
            $created = $roleNow === 'incharge' ? (int)current_user_id() : null;
            $stmt = mysqli_prepare($conn, 'INSERT INTO teacher (Name,Email,Username,Password,Department,Designation,CreatedBy) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $email, $username, $hash, $department, $designation, $created);
        } elseif ($type === 'student') {
            $created = $roleNow === 'incharge' ? (int)current_user_id() : null;
            $stmt = mysqli_prepare($conn, 'INSERT INTO student (Name,RollNo,Email,Username,Password,Department,Semester,CreatedBy) VALUES (?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'sssssssi', $name, $rollno, $email, $username, $hash, $department, $semester, $created);
        } else {
            $stmt = null; add_flash('error', 'Invalid account type.');
        }
        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) add_flash('success', 'Account created successfully.');
            else                            add_flash('error',   'Failed: ' . mysqli_error($conn));
            mysqli_stmt_close($stmt);
        }
    }
    redirect_to($roleNow === 'admin' ? 'admin/users.php' : 'incharge/users.php');
}

// ── Fetch ─────────────────────────────────────────────────────────────────
$incharges = fetch_all('SELECT * FROM academicsincharge ORDER BY InchargeID DESC');
$teachers  = fetch_all('SELECT * FROM teacher ORDER BY TeacherID DESC');
$students  = fetch_all('SELECT * FROM student ORDER BY StudentID DESC');

dashboard_start('User Management', 'Create and manage user accounts');
?>

<!-- ═══════════════════ Edit Modal ═══════════════════ -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editUserBody"><!-- filled by JS --></div>
    </div>
  </div>
</div>

<!-- ═══════════════════ Main Layout ═══════════════════ -->
<div class="row g-4">

  <!-- ── ADD FORM (unchanged) ── -->
  <div class="col-lg-4">
    <div class="panel-card">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add Account</h5>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Account Type</label>
          <select class="form-select" name="type" id="userType"
            onchange="document.querySelectorAll('.teacher-only').forEach(e=>e.classList.toggle('d-none',this.value!=='teacher'));document.querySelectorAll('.student-only').forEach(e=>e.classList.toggle('d-none',this.value!=='student'))">
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <?php if ($canManageIncharge): ?><option value="incharge">Academic In-Charge</option><?php endif; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label fw-bold">Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label fw-bold">Email</label><input type="email" class="form-control" name="email" required></div>
        <div class="mb-2"><label class="form-label fw-bold">Username</label><input class="form-control" name="username" required></div>
        <div class="mb-2"><label class="form-label fw-bold">Password</label><input class="form-control" name="password" value="123456"></div>
        <div class="mb-2"><label class="form-label fw-bold">Department</label><input class="form-control" name="department"></div>
        <div class="mb-2 teacher-only"><label class="form-label fw-bold">Designation</label><input class="form-control" name="designation"></div>
        <div class="mb-2 student-only d-none"><label class="form-label fw-bold">Roll No</label><input class="form-control" name="rollno"></div>
        <div class="mb-3 student-only d-none"><label class="form-label fw-bold">Semester</label><input class="form-control" name="semester" placeholder="e.g. 2nd"></div>
        <button class="btn btn-main w-100">Save Account</button>
      </form>
    </div>
  </div>

  <!-- ── TABLES ── -->
  <div class="col-lg-8">
    <div class="data-card">
      <ul class="nav nav-pills mb-3" role="tablist">
        <?php if ($canManageIncharge): ?>
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#incharges">In-Charge</button></li>
        <?php endif; ?>
        <li class="nav-item"><button class="nav-link <?= $canManageIncharge ? '' : 'active' ?>" data-bs-toggle="pill" data-bs-target="#teachers">Teachers</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#students">Students</button></li>
      </ul>

      <div class="tab-content">

        <!-- In-Charges -->
        <?php if ($canManageIncharge): ?>
        <div class="tab-pane fade show active" id="incharges">
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Name</th><th>Email</th><th>Department</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($incharges as $u): ?>
                <tr>
                  <td><?= h($u['Name']) ?></td>
                  <td><?= h($u['Email']) ?></td>
                  <td><?= h($u['Department']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-warning me-1"
                      onclick="openEditUser('incharge',<?= (int)$u['InchargeID'] ?>,<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Edit</button>
                    <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete account?')"
                      href="?type=incharge&delete=<?= (int)$u['InchargeID'] ?>">Delete</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>

        <!-- Teachers -->
        <div class="tab-pane fade <?= $canManageIncharge ? '' : 'show active' ?>" id="teachers">
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Designation</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($teachers as $u): ?>
                <tr>
                  <td><?= h($u['Name']) ?></td>
                  <td><?= h($u['Email']) ?></td>
                  <td><?= h($u['Department']) ?></td>
                  <td><?= h($u['Designation']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-warning me-1"
                      onclick="openEditUser('teacher',<?= (int)$u['TeacherID'] ?>,<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Edit</button>
                    <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete account?')"
                      href="?type=teacher&delete=<?= (int)$u['TeacherID'] ?>">Delete</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Students -->
        <div class="tab-pane fade" id="students">
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Name</th><th>Roll No</th><th>Email</th><th>Department</th><th>Semester</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($students as $u): ?>
                <tr>
                  <td><?= h($u['Name']) ?></td>
                  <td><?= h($u['RollNo']) ?></td>
                  <td><?= h($u['Email']) ?></td>
                  <td><?= h($u['Department']) ?></td>
                  <td><?= h($u['Semester']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-warning me-1"
                      onclick="openEditUser('student',<?= (int)$u['StudentID'] ?>,<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Edit</button>
                    <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete account?')"
                      href="?type=student&delete=<?= (int)$u['StudentID'] ?>">Delete</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /tab-content -->
    </div>
  </div>
</div>

<!-- ═══════════════════ Edit Modal JS ═══════════════════ -->
<script>
function openEditUser(type, id, d) {
  const f = (label, name, val, inputType='text') => `
    <div class="mb-2">
      <label class="form-label fw-bold">${label}</label>
      <input class="form-control" name="${name}" type="${inputType}" value="${String(val??'').replace(/"/g,'&quot;')}">
    </div>`;

  let extra = '';
  if (type === 'teacher')  extra = f('Designation', 'designation', d.Designation);
  if (type === 'student')  extra = f('Roll No',     'rollno',      d.RollNo) +
                                   f('Semester',     'semester',    d.Semester);

  document.getElementById('editUserBody').innerHTML = `
    <form method="POST">
      <input type="hidden" name="action"    value="update">
      <input type="hidden" name="type"      value="${type}">
      <input type="hidden" name="record_id" value="${id}">
      ${f('Name',       'name',       d.Name)}
      ${f('Email',      'email',      d.Email, 'email')}
      ${f('Username',   'username',   d.Username)}
      <div class="mb-2">
        <label class="form-label fw-bold">Password <small class="text-muted fw-normal">(leave blank to keep current)</small></label>
        <input class="form-control" name="password" type="password" placeholder="New password">
      </div>
      ${f('Department', 'department', d.Department)}
      ${extra}
      <div class="text-end mt-3">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-main">Update</button>
      </div>
    </form>`;

  new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>

<?php dashboard_end(); ?>