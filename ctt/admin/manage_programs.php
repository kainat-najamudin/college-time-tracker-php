<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Program WHERE ProgramID=$id");
    $success = "Program deleted successfully.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id     = (int)($_POST['program_id'] ?? 0);
    $name   = mysqli_real_escape_string($conn, trim($_POST['name']));
    $type   = mysqli_real_escape_string($conn, trim($_POST['type']));

    if (empty($name)) { $error = "Program name is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Program SET Name='$name', AcademicSystemType='$type' WHERE ProgramID=$id")
                ? $success = "Program updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Program (Name, AcademicSystemType) VALUES ('$name','$type')")
                ? $success = "Program added." : $error = mysqli_error($conn);
        }
    }
}

$list = mysqli_query($conn, "SELECT p.*, (SELECT COUNT(*) FROM Class c WHERE c.ProgramID=p.ProgramID) as ClassCount FROM Program p ORDER BY ProgramID DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Programs</div>
            <h4><i class="fas fa-graduation-cap me-2"></i>Programs</h4>
            <p>Manage academic programs (BSCS, BBA, etc.)</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#progModal" onclick="resetForm('program_id','f_prog_name','f_prog_type')">
            <i class="fas fa-plus me-2"></i>Add Program
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-list me-2"></i>All Programs</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>#</th><th>Program Name</th><th>Type</th><th>Classes</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($list)==0): ?>
                    <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-graduation-cap fa-2x mb-2 d-block"></i>No programs found</td></tr>
                <?php else: $i=1; while($row=mysqli_fetch_assoc($list)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;flex-shrink:0;">
                                <?=strtoupper(substr($row['Name'],0,2))?>
                            </div>
                            <strong><?=htmlspecialchars($row['Name'])?></strong>
                        </div>
                    </td>
                    <td><?php if($row['AcademicSystemType']): ?><span style="background:#eff6ff;color:#2563eb;padding:3px 12px;border-radius:50px;font-size:0.8rem;font-weight:600;"><?=htmlspecialchars($row['AcademicSystemType'])?></span><?php else: echo '—'; endif; ?></td>
                    <td><span style="background:#f0fdf4;color:#059669;padding:3px 12px;border-radius:50px;font-size:0.8rem;font-weight:600;"><?=$row['ClassCount']?> Classes</span></td>
                    <td>
                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#progModal"
                            onclick="fillEdit('program_id','f_prog_name','f_prog_type',<?=$row['ProgramID']?>,'<?=addslashes($row['Name'])?>','<?=addslashes($row['AcademicSystemType']??'')?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?=$row['ProgramID']?>" class="btn-action btn-delete ms-1" onclick="return confirm('Delete this program?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="progModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="progModalTitle"><i class="fas fa-graduation-cap me-2"></i>Add Program</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="program_id" id="program_id" value="0">
                    <div class="mb-3">
                        <label class="form-label">Program Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="f_prog_name" placeholder="e.g. BSCS, BBA, BSIT" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic System Type</label>
                        <select class="form-select" name="type" id="f_prog_type">
                            <option value="">-- Select Type --</option>
                            <option value="Semester">Semester Based</option>
                            <option value="Annual">Annual Based</option>
                            <option value="Quarter">Quarter Based</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetForm(idField, nameField, typeField) {
    document.getElementById('progModalTitle').innerHTML = '<i class="fas fa-graduation-cap me-2"></i>Add Program';
    document.getElementById(idField).value   = '0';
    document.getElementById(nameField).value = '';
    if (typeField) document.getElementById(typeField).value = '';
}
function fillEdit(idField, nameField, typeField, id, name, type) {
    document.getElementById('progModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Program';
    document.getElementById(idField).value   = id;
    document.getElementById(nameField).value = name;
    if (typeField) document.getElementById(typeField).value = type;
}
</script>
</body>
</html>
