<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Subject WHERE SubjectID=$id");
    $success = "Subject deleted.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = (int)($_POST['subject_id'] ?? 0);
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $class_id = (int)$_POST['class_id'];
    $semester = mysqli_real_escape_string($conn, trim($_POST['semester']));
    $dispord  = (int)$_POST['display_order'];

    if (empty($name)) { $error = "Subject name is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Subject SET Name='$name',ClassID=$class_id,Semester='$semester',DisplayOrder=$dispord WHERE SubjectID=$id")
                ? $success = "Subject updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Subject (Name,ClassID,Semester,DisplayOrder) VALUES ('$name',$class_id,'$semester',$dispord)")
                ? $success = "Subject added." : $error = mysqli_error($conn);
        }
    }
}

$classes  = mysqli_query($conn, "SELECT * FROM Class ORDER BY Name");
$f_class  = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$where    = $f_class ? "WHERE s.ClassID=$f_class" : '';
$list     = mysqli_query($conn, "SELECT s.*, c.Name as ClassName FROM Subject s LEFT JOIN Class c ON s.ClassID=c.ClassID $where ORDER BY s.ClassID, s.DisplayOrder");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Subjects</div>
            <h4><i class="fas fa-book me-2"></i>Subjects</h4>
            <p>Manage subjects assigned to each class</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#subjModal" onclick="resetSubjForm()">
            <i class="fas fa-plus me-2"></i>Add Subject
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Filter -->
    <div class="form-card mb-4" style="padding:1rem 1.5rem;">
        <form method="GET" class="d-flex gap-3 align-items-end">
            <div>
                <label class="form-label">Filter by Class</label>
                <select class="form-select" name="class_id" onchange="this.form.submit()" style="min-width:220px;">
                    <option value="0">All Classes</option>
                    <?php
                    $cls_list = mysqli_query($conn, "SELECT * FROM Class ORDER BY Name");
                    while($c=mysqli_fetch_assoc($cls_list)):
                    ?><option value="<?=$c['ClassID']?>" <?=$f_class==$c['ClassID']?'selected':''?>><?=htmlspecialchars($c['Name'])?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if ($f_class): ?><a href="manage_subjects.php" class="btn btn-light" style="border-radius:10px; margin-bottom:2px;">Clear Filter</a><?php endif; ?>
        </form>
    </div>

    <div class="data-table-card">
        <div class="data-table-header"><div class="data-table-title"><i class="fas fa-list me-2"></i>Subjects List</div></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>#</th><th>Subject Name</th><th>Class</th><th>Semester</th><th>Display Order</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($list)==0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-book fa-2x mb-2 d-block"></i>No subjects found</td></tr>
                <?php else: $i=1; while($row=mysqli_fetch_assoc($list)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#059669,#34d399);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.8rem;flex-shrink:0;">
                                <?=strtoupper(substr($row['Name'],0,2))?>
                            </div>
                            <strong><?=htmlspecialchars($row['Name'])?></strong>
                        </div>
                    </td>
                    <td><?=htmlspecialchars($row['ClassName']??'—')?></td>
                    <td><?=htmlspecialchars($row['Semester']??'—')?></td>
                    <td><span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:50px;font-size:0.8rem;font-weight:600;"><?=$row['DisplayOrder']?></span></td>
                    <td>
                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#subjModal"
                            onclick="editSubj(<?=htmlspecialchars(json_encode($row))?>)"><i class="fas fa-edit"></i></button>
                        <a href="?delete=<?=$row['SubjectID']?>" class="btn-action btn-delete ms-1" onclick="return confirm('Delete this subject?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="subjModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="subjModalTitle"><i class="fas fa-book me-2"></i>Add Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="subject_id" id="subjId" value="0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="sub_name" placeholder="e.g. Data Structures" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id" id="sub_class">
                                <option value="0">-- Select Class --</option>
                                <?php
                                $cls2 = mysqli_query($conn, "SELECT * FROM Class ORDER BY Name");
                                while($c=mysqli_fetch_assoc($cls2)): ?>
                                <option value="<?=$c['ClassID']?>"><?=htmlspecialchars($c['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" id="sub_semester">
                                <option value="">-- Select --</option>
                                <?php foreach(['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?>
                                <option value="<?=$s?> Semester"><?=$s?> Semester</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" id="sub_order" value="1" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetSubjForm() {
    document.getElementById('subjModalTitle').innerHTML = '<i class="fas fa-book me-2"></i>Add Subject';
    document.getElementById('subjId').value      = '0';
    document.getElementById('sub_name').value    = '';
    document.getElementById('sub_class').value   = '0';
    document.getElementById('sub_semester').value= '';
    document.getElementById('sub_order').value   = '1';
}
function editSubj(d) {
    document.getElementById('subjModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Subject';
    document.getElementById('subjId').value      = d.SubjectID;
    document.getElementById('sub_name').value    = d.Name;
    document.getElementById('sub_class').value   = d.ClassID   || '0';
    document.getElementById('sub_semester').value= d.Semester  || '';
    document.getElementById('sub_order').value   = d.DisplayOrder || 1;
}
</script>
</body>
</html>
