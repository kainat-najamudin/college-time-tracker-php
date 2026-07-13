<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Class WHERE ClassID=$id");
    $success = "Class deleted successfully.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id         = (int)($_POST['class_id'] ?? 0);
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $session    = mysqli_real_escape_string($conn, trim($_POST['session']));
    $tname      = mysqli_real_escape_string($conn, trim($_POST['ttdname']));
    $cursem     = mysqli_real_escape_string($conn, trim($_POST['current_semester']));
    $isactive   = isset($_POST['isactive']) ? 1 : 0;
    $program_id = (int)$_POST['program_id'];

    if (empty($name)) { $error = "Class name is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Class SET Name='$name',Session='$session',TTDName='$tname',CurrentSemester='$cursem',IsActive=$isactive,ProgramID=$program_id WHERE ClassID=$id")
                ? $success = "Class updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Class (Name,Session,TTDName,CurrentSemester,IsActive,ProgramID) VALUES ('$name','$session','$tname','$cursem',$isactive,$program_id)")
                ? $success = "Class added." : $error = mysqli_error($conn);
        }
    }
}

$programs = mysqli_query($conn, "SELECT * FROM Program ORDER BY Name");
$list     = mysqli_query($conn, "SELECT c.*, p.Name as ProgramName FROM Class c LEFT JOIN Program p ON c.ProgramID=p.ProgramID ORDER BY c.ClassID DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Classes</div>
            <h4><i class="fas fa-school me-2"></i>Classes</h4>
            <p>Manage all academic classes</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#classModal" onclick="resetClassForm()">
            <i class="fas fa-plus me-2"></i>Add Class
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header"><div class="data-table-title"><i class="fas fa-list me-2"></i>All Classes</div></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>#</th><th>Class Name</th><th>Program</th><th>Session</th><th>Current Semester</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($list)==0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-school fa-2x mb-2 d-block"></i>No classes found</td></tr>
                <?php else: $i=1; while($row=mysqli_fetch_assoc($list)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><strong><?=htmlspecialchars($row['Name'])?></strong><?php if($row['TTDName']): ?><br><small class="text-muted"><?=htmlspecialchars($row['TTDName'])?></small><?php endif; ?></td>
                    <td><?=htmlspecialchars($row['ProgramName']??'—')?></td>
                    <td><?=htmlspecialchars($row['Session']??'—')?></td>
                    <td><?=htmlspecialchars($row['CurrentSemester']??'—')?></td>
                    <td><?php if($row['IsActive']): ?><span class="badge-active"><i class="fas fa-circle me-1" style="font-size:0.6rem;"></i>Active</span><?php else: ?><span class="badge-inactive"><i class="fas fa-circle me-1" style="font-size:0.6rem;"></i>Inactive</span><?php endif; ?></td>
                    <td>
                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#classModal"
                            onclick="editClass(<?=htmlspecialchars(json_encode($row))?>)"><i class="fas fa-edit"></i></button>
                        <a href="?delete=<?=$row['ClassID']?>" class="btn-action btn-delete ms-1" onclick="return confirm('Delete this class?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="classModalTitle"><i class="fas fa-school me-2"></i>Add Class</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="class_id" id="classId" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="c_name" placeholder="e.g. BSCS 3rd Semester" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program</label>
                            <select class="form-select" name="program_id" id="c_program">
                                <option value="0">-- Select Program --</option>
                                <?php $programs_arr = []; while($p=mysqli_fetch_assoc($programs)){ $programs_arr[]=$p; echo "<option value='{$p['ProgramID']}'>{$p['Name']}</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session</label>
                            <input type="text" class="form-control" name="session" id="c_session" placeholder="e.g. 2023-2027">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TTD Name</label>
                            <input type="text" class="form-control" name="ttdname" id="c_ttdname" placeholder="Timetable display name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Semester</label>
                            <select class="form-select" name="current_semester" id="c_cursem">
                                <option value="">-- Select --</option>
                                <?php foreach(['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?><option value="<?=$s?>"><?=$s?> Semester</option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="isactive" id="c_isactive" checked style="width:2.5rem;height:1.3rem;">
                                <label class="form-check-label ms-2 fw-600" for="c_isactive">Active Class</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetClassForm() {
    document.getElementById('classModalTitle').innerHTML = '<i class="fas fa-school me-2"></i>Add Class';
    document.getElementById('classId').value = '0';
    ['c_name','c_session','c_ttdname'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('c_program').value = '0';
    document.getElementById('c_cursem').value   = '';
    document.getElementById('c_isactive').checked = true;
}
function editClass(d) {
    document.getElementById('classModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Class';
    document.getElementById('classId').value     = d.ClassID;
    document.getElementById('c_name').value      = d.Name;
    document.getElementById('c_session').value   = d.Session   || '';
    document.getElementById('c_ttdname').value   = d.TTDName   || '';
    document.getElementById('c_program').value   = d.ProgramID || '0';
    document.getElementById('c_cursem').value    = d.CurrentSemester || '';
    document.getElementById('c_isactive').checked = d.IsActive == 1;
}
</script>
</body>
</html>
