<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $check = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM TimetableCellDetail WHERE SectionID=$id"));
    if ($check[0] > 0) {
        $error = "Cannot delete: This section is used in timetable cells.";
    } else {
        mysqli_query($conn, "DELETE FROM Section WHERE SectionID=$id")
            ? $success = "Section deleted successfully."
            : $error   = mysqli_error($conn);
    }
}

// TOGGLE ACTIVE
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $cur = mysqli_fetch_row(mysqli_query($conn, "SELECT IsActive FROM Section WHERE SectionID=$id"));
    $new = $cur[0] == 1 ? 0 : 1;
    mysqli_query($conn, "UPDATE Section SET IsActive=$new WHERE SectionID=$id");
    $success = "Section status updated.";
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sec_action'])) {
    $id      = (int)($_POST['sec_id'] ?? 0);
    $name    = mysqli_real_escape_string($conn, trim($_POST['name']));
    $ttdname = mysqli_real_escape_string($conn, trim($_POST['ttdname'] ?? ''));
    $sem     = mysqli_real_escape_string($conn, trim($_POST['semester'] ?? ''));
    $order   = (int)($_POST['display_order'] ?? 0);
    $subjid  = (int)($_POST['subject_id']    ?? 0);
    $tchid   = (int)($_POST['teacher_id']    ?? 0);

    if (empty($name)) {
        $error = "Section name is required.";
    } else {
        // Duplicate check
        $dupWhere = "Name='$name'" . ($id > 0 ? " AND SectionID != $id" : "");
        $dup = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM Section WHERE $dupWhere"));
        if ($dup[0] > 0) {
            $error = "Section name '$name' already exists.";
        } elseif ($id > 0) {
            mysqli_query($conn,
                "UPDATE Section SET Name='$name', TTDName='$ttdname', Semester='$sem',
                 DisplayOrder=$order, SubjectID=" . ($subjid ?: 'NULL') . ",
                 TeacherID=" . ($tchid ?: 'NULL') . "
                 WHERE SectionID=$id")
                ? $success = "Section updated successfully."
                : $error   = mysqli_error($conn);
        } else {
            mysqli_query($conn,
                "INSERT INTO Section (Name, TTDName, Semester, DisplayOrder, IsActive, SubjectID, TeacherID)
                 VALUES ('$name','$ttdname','$sem',$order,1,
                 " . ($subjid ?: 'NULL') . "," . ($tchid ?: 'NULL') . ")")
                ? $success = "Section added successfully."
                : $error   = mysqli_error($conn);
        }
    }
}

// Filters
$search   = mysqli_real_escape_string($conn, $_GET['search']     ?? '');
$filterSem = mysqli_real_escape_string($conn, $_GET['filter_sem'] ?? '');

$where = "WHERE 1=1";
if ($search)    $where .= " AND (s.Name LIKE '%$search%' OR s.TTDName LIKE '%$search%' OR s.Semester LIKE '%$search%')";
if ($filterSem) $where .= " AND s.Semester='$filterSem'";

$sections = mysqli_query($conn, "
    SELECT s.*,
           sub.Name as SubjectName,
           t.Name   as TeacherName,
           (SELECT COUNT(*) FROM TimetableCellDetail tcd WHERE tcd.SectionID=s.SectionID) as UsageCount
    FROM Section s
    LEFT JOIN Subject sub ON sub.SubjectID = s.SubjectID
    LEFT JOIN Teacher t   ON t.TeacherID   = s.TeacherID
    $where
    ORDER BY s.Semester, s.DisplayOrder, s.Name
");

$total   = mysqli_num_rows($sections);
$subjects = mysqli_query($conn, "SELECT * FROM Subject ORDER BY Name");
$teachers = mysqli_query($conn, "SELECT * FROM Teacher ORDER BY Name");
$subjects2 = mysqli_query($conn, "SELECT * FROM Subject ORDER BY Name");
$teachers2 = mysqli_query($conn, "SELECT * FROM Teacher ORDER BY Name");

// Get distinct semesters for filter
$semesters = mysqli_query($conn, "SELECT DISTINCT Semester FROM Section WHERE Semester != '' ORDER BY Semester");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - CTT Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        .sec-avatar {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: #fff; font-size: 1.1rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .info-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 600;
        }
        .chip-subj { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .chip-tch  { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .chip-sem  { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .chip-ord  { background: #f0fdf4; color: #059669; border: 1px solid #bbf7d0; }
        .chip-use  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .chip-use-zero { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }
        .toggle-active   { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; cursor: pointer; border: none; }
        .toggle-inactive { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; cursor: pointer; border: none; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom">
                <a href="dashboard.php">Dashboard</a>
                <i class="fas fa-chevron-right"></i>
                <span>Sections</span>
            </div>
            <h4><i class="fas fa-layer-group me-2"></i>Manage Sections</h4>
            <p>Add and manage class sections with assigned subjects and teachers</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#sectionModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Add Section
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

    <!-- Stats -->
    <?php
    $totalSec    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Section"))[0];
    $activeSec   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM Section WHERE IsActive=1"))[0];
    $usedSec     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT SectionID) FROM TimetableCellDetail WHERE SectionID IS NOT NULL AND SectionID > 0"))[0];
    $totalSem    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT Semester) FROM Section WHERE Semester != ''"))[0];
    ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;"><i class="fas fa-layer-group" style="color:#2563eb;"></i></div><div class="stat-value"><?=$totalSec?></div><div class="stat-label">Total Sections</div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card"><div class="stat-icon" style="background:#f0fdf4;"><i class="fas fa-check-circle" style="color:#059669;"></i></div><div class="stat-value"><?=$activeSec?></div><div class="stat-label">Active Sections</div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card"><div class="stat-icon" style="background:#f5f3ff;"><i class="fas fa-calendar-check" style="color:#7c3aed;"></i></div><div class="stat-value"><?=$usedSec?></div><div class="stat-label">Used in Timetables</div></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card"><div class="stat-icon" style="background:#fff7ed;"><i class="fas fa-graduation-cap" style="color:#ea580c;"></i></div><div class="stat-value"><?=$totalSem?></div><div class="stat-label">Semesters Covered</div></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="form-card mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <div style="position:relative;">
                    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, TTD name, semester..." value="<?=htmlspecialchars($search)?>" style="padding-left:40px;">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter by Semester</label>
                <select name="filter_sem" class="form-select">
                    <option value="">All Semesters</option>
                    <?php while($sem = mysqli_fetch_assoc($semesters)): ?>
                    <option value="<?=htmlspecialchars($sem['Semester'])?>" <?=$filterSem==$sem['Semester']?'selected':''?>>
                        <?=htmlspecialchars($sem['Semester'])?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-primary-custom flex-fill"><i class="fas fa-filter me-2"></i>Filter</button>
                <a href="manage_sections.php" class="btn btn-light" style="border-radius:12px;padding:10px 16px;"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-table-card">
        <div class="data-table-header">
            <div class="data-table-title"><i class="fas fa-layer-group me-2 text-primary"></i>All Sections
                <span style="font-size:0.8rem;color:#94a3b8;margin-left:8px;">(<?=$total?> found)</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>TTD Name</th>
                        <th>Semester</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Order</th>
                        <th>Timetable Use</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($total == 0): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-layer-group fa-2x d-block mb-3"></i>
                            <?= ($search || $filterSem) ? 'No sections match your filter.' : 'No sections added yet.' ?>
                        </td>
                    </tr>
                <?php else: $i=1; while($s = mysqli_fetch_assoc($sections)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="sec-avatar"><?=strtoupper(substr($s['Name'],0,1))?></div>
                            <div>
                                <div style="font-weight:700;color:#1e293b;"><?=htmlspecialchars($s['Name'])?></div>
                                <div style="font-size:0.75rem;color:#94a3b8;">ID: <?=$s['SectionID']?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if($s['TTDName']): ?>
                            <span style="font-size:0.85rem;color:#374151;font-weight:600;"><?=htmlspecialchars($s['TTDName'])?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($s['Semester']): ?>
                            <span class="info-chip chip-sem"><i class="fas fa-graduation-cap"></i><?=htmlspecialchars($s['Semester'])?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($s['SubjectName']): ?>
                            <span class="info-chip chip-subj"><i class="fas fa-book"></i><?=htmlspecialchars($s['SubjectName'])?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.82rem;">Not assigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($s['TeacherName']): ?>
                            <span class="info-chip chip-tch"><i class="fas fa-chalkboard-teacher"></i><?=htmlspecialchars($s['TeacherName'])?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.82rem;">Not assigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="info-chip chip-ord"><i class="fas fa-sort-numeric-up"></i><?=$s['DisplayOrder']?></span>
                    </td>
                    <td>
                        <?php if($s['UsageCount'] > 0): ?>
                            <span class="info-chip chip-use"><i class="fas fa-calendar-check"></i><?=$s['UsageCount']?> cell(s)</span>
                        <?php else: ?>
                            <span class="info-chip chip-use-zero"><i class="fas fa-minus"></i>Not used</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?toggle=<?=$s['SectionID']?><?= $search?"&search=$search":'' ?><?= $filterSem?"&filter_sem=$filterSem":'' ?>"
                           class="<?=$s['IsActive']?'toggle-active':'toggle-inactive'?>"
                           onclick="return confirm('Toggle status?')">
                            <?=$s['IsActive'] ? 'Active' : 'Inactive'?>
                        </a>
                    </td>
                    <td>
                        <button class="btn-action btn-edit" title="Edit"
                            data-bs-toggle="modal" data-bs-target="#sectionModal"
                            onclick="editSection(
                                <?=$s['SectionID']?>,
                                '<?=addslashes($s['Name'])?>',
                                '<?=addslashes($s['TTDName']??'')?>',
                                '<?=addslashes($s['Semester']??'')?>',
                                <?=(int)$s['DisplayOrder']?>,
                                <?=(int)($s['SubjectID']??0)?>,
                                <?=(int)($s['TeacherID']??0)?>
                            )">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?=$s['SectionID']?>" class="btn-action btn-delete" title="Delete"
                            onclick="return confirm('Delete section \'<?=addslashes($s['Name'])?>\'?')">
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

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="secModalTitle">
                    <i class="fas fa-layer-group me-2"></i>Add Section
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="sec_action" value="1">
                <div class="modal-body p-4">
                    <input type="hidden" name="sec_id" id="secId" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="secName" class="form-control"
                                   placeholder="e.g. A, B, Morning, Evening" required maxlength="50">
                            <div class="form-text">Short, unique section identifier</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TTD Name</label>
                            <input type="text" name="ttdname" id="secTTDName" class="form-control"
                                   placeholder="Display name in timetable" maxlength="100">
                            <div class="form-text">Name shown on printed timetable</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" id="secSemester" class="form-control"
                                   placeholder="e.g. 1st, 2nd, 3rd..." maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="secOrder" class="form-control"
                                   value="0" min="0" max="999">
                            <div class="form-text">Lower number = shown first</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Subject</label>
                            <select name="subject_id" id="secSubjectId" class="form-select">
                                <option value="">-- No Subject --</option>
                                <?php while($sub = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?=$sub['SubjectID']?>"><?=htmlspecialchars($sub['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Teacher</label>
                            <select name="teacher_id" id="secTeacherId" class="form-select">
                                <option value="">-- No Teacher --</option>
                                <?php while($tch = mysqli_fetch_assoc($teachers)): ?>
                                <option value="<?=$tch['TeacherID']?>"><?=htmlspecialchars($tch['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Quick Fill -->
                        <div class="col-12">
                            <label class="form-label" style="font-size:0.82rem;color:#64748b;">Quick Fill — Section Name</label>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach(['A','B','C','D','E','F','Morning','Evening','Regular','Weekend'] as $q): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    style="border-radius:50px;font-size:0.78rem;"
                                    onclick="document.getElementById('secName').value='<?=$q?>'">
                                    <?=$q?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetForm() {
    document.getElementById('secModalTitle').innerHTML = '<i class="fas fa-layer-group me-2"></i>Add Section';
    document.getElementById('secId').value        = '0';
    document.getElementById('secName').value      = '';
    document.getElementById('secTTDName').value   = '';
    document.getElementById('secSemester').value  = '';
    document.getElementById('secOrder').value     = '0';
    document.getElementById('secSubjectId').value = '';
    document.getElementById('secTeacherId').value = '';
}
function editSection(id, name, ttdname, semester, order, subjectId, teacherId) {
    document.getElementById('secModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Section';
    document.getElementById('secId').value        = id;
    document.getElementById('secName').value      = name;
    document.getElementById('secTTDName').value   = ttdname;
    document.getElementById('secSemester').value  = semester;
    document.getElementById('secOrder').value     = order;
    document.getElementById('secSubjectId').value = subjectId || '';
    document.getElementById('secTeacherId').value = teacherId || '';
}
</script>
</body>
</html>
