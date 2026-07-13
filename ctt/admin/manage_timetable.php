<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

// DELETE TIMETABLE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM TimetableCellDetail WHERE TTCID IN (SELECT TTCID FROM TimetableCell WHERE TTID=$id)");
    mysqli_query($conn, "DELETE FROM TimetableCell WHERE TTID=$id");
    mysqli_query($conn, "DELETE FROM Timetable WHERE TTID=$id");
    $success = "Timetable deleted successfully.";
}

// ADD / EDIT TIMETABLE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tt_action'])) {
    $id       = (int)($_POST['tt_id'] ?? 0);
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $eff_from = $_POST['effective_from'];

    if (empty($name)) { $error = "Timetable name is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Timetable SET Name='$name', WithEffectiveFrom='$eff_from' WHERE TTID=$id")
                ? $success = "Timetable updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Timetable (Name, WithEffectiveFrom) VALUES ('$name','$eff_from')")
                ? $success = "Timetable created." : $error = mysqli_error($conn);
        }
    }
}

$list     = mysqli_query($conn, "SELECT tt.*, (SELECT COUNT(*) FROM TimetableCell tc WHERE tc.TTID=tt.TTID) as CellCount FROM Timetable tt ORDER BY tt.TTID DESC");
$classes  = mysqli_query($conn, "SELECT * FROM Class WHERE IsActive=1 ORDER BY Name");
$periods  = mysqli_query($conn, "SELECT * FROM Period WHERE IsBreak=0 ORDER BY DisplayOrder");
$teachers = mysqli_query($conn, "SELECT * FROM Teacher ORDER BY Name");
$subjects = mysqli_query($conn, "SELECT s.*, c.Name as ClassName FROM Subject s LEFT JOIN Class c ON s.ClassID=c.ClassID ORDER BY c.Name, s.Name");
$rooms    = mysqli_query($conn, "SELECT * FROM Room ORDER BY Title");
$sections = mysqli_query($conn, "SELECT * FROM Section ORDER BY Name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Management - CTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        .tt-card {
            background: #fff; border-radius: 16px; padding: 1.5rem;
            border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s; height: 100%;
        }
        .tt-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .tt-icon { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg,#1a3c6e,#2563eb); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; margin-bottom: 1rem; }
        .timetable-grid { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .timetable-grid th { background: #1a3c6e; color: #fff; padding: 10px 8px; text-align: center; font-weight: 700; border: 1px solid #2563eb; }
        .timetable-grid td { padding: 6px; border: 1px solid #e5e7eb; text-align: center; vertical-align: middle; min-width: 110px; }
        .timetable-grid .period-col { background: #f8faff; font-weight: 700; color: #1a3c6e; min-width: 90px; }
        .cell-filled { background: linear-gradient(135deg,#eff6ff,#e0f2fe); border-radius: 8px; padding: 6px 4px; cursor: pointer; border: 1px solid #bfdbfe; }
        .cell-filled:hover { background: linear-gradient(135deg,#dbeafe,#bfdbfe); }
        .cell-empty { background: #fafafa; border-radius: 8px; cursor: pointer; padding: 8px; color: #cbd5e1; font-size: 0.75rem; }
        .cell-empty:hover { background: #eff6ff; color: #2563eb; }
        .day-header { font-size: 0.82rem; font-weight: 700; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<main class="main-content" id="mainContent">
    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Timetable</div>
            <h4><i class="fas fa-calendar-alt me-2"></i>Timetable Management</h4>
            <p>Create and manage academic timetables</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#ttModal" onclick="resetTTForm()">
            <i class="fas fa-plus me-2"></i>New Timetable
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Timetable Cards -->
    <div class="row g-3 mb-4">
        <?php
        $tt_arr = [];
        while ($tt = mysqli_fetch_assoc($list)) $tt_arr[] = $tt;
        if (empty($tt_arr)): ?>
        <div class="col-12">
            <div class="text-center py-5" style="background:#fff;border-radius:16px;border:2px dashed #e5e7eb;">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No timetables yet</h5>
                <p class="text-muted mb-3">Create your first timetable to get started</p>
                <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#ttModal" onclick="resetTTForm()">
                    <i class="fas fa-plus me-2"></i>Create Timetable
                </button>
            </div>
        </div>
        <?php else: foreach ($tt_arr as $tt): ?>
        <div class="col-xl-4 col-md-6">
            <div class="tt-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="tt-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="d-flex gap-1">
                        <button class="btn-action btn-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#ttModal"
                            onclick="editTT(<?=$tt['TTID']?>,'<?=addslashes($tt['Name'])?>','<?=$tt['WithEffectiveFrom']??''?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-view" title="Build Timetable" onclick="openBuilder(<?=$tt['TTID']?>,'<?=addslashes($tt['Name'])?>');document.getElementById('builderSection').scrollIntoView({behavior:'smooth'})">
                            <i class="fas fa-table"></i>
                        </button>
                        <a href="?delete=<?=$tt['TTID']?>" class="btn-action btn-delete" onclick="return confirm('Delete this timetable and all its cells?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <h5 style="font-weight:800;color:#1e293b;margin-top:0.5rem;"><?=htmlspecialchars($tt['Name'])?></h5>
                <div class="d-flex gap-3 mt-2">
                    <span style="font-size:0.82rem;color:#64748b;"><i class="fas fa-table me-1"></i><?=$tt['CellCount']?> cells</span>
                    <?php if($tt['WithEffectiveFrom']): ?>
                    <span style="font-size:0.82rem;color:#64748b;"><i class="fas fa-calendar me-1"></i><?=date('M d, Y', strtotime($tt['WithEffectiveFrom']))?></span>
                    <?php endif; ?>
                </div>
                <div style="margin-top:0.75rem;font-size:0.78rem;color:#94a3b8;"><i class="fas fa-clock me-1"></i>Updated: <?=date('M d, Y', strtotime($tt['LastUpdatedOn']))?></div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Timetable Builder -->
    <div id="builderSection" style="display:none;">
        <div class="form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 style="font-weight:800;color:#1a3c6e;margin:0;" id="builderTitle">Timetable Builder</h5>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">Click any cell to assign subject, teacher, and room</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select" id="filterClass" style="width:200px;" onchange="loadTimetableGrid()">
                        <option value="">Select Class</option>
                        <?php while($c=mysqli_fetch_assoc($classes)): ?><option value="<?=$c['ClassID']?>"><?=htmlspecialchars($c['Name'])?></option><?php endwhile; ?>
                    </select>
                    <button class="btn btn-light" onclick="document.getElementById('builderSection').style.display='none'" style="border-radius:10px;">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
            <div id="timetableGridContainer">
                <div class="text-center text-muted py-4"><i class="fas fa-arrow-up me-2"></i>Select a class to view timetable grid</div>
            </div>
        </div>
    </div>
</main>

<!-- CREATE/EDIT TIMETABLE MODAL -->
<div class="modal fade" id="ttModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="ttModalTitle"><i class="fas fa-calendar-alt me-2"></i>New Timetable</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <input type="hidden" name="tt_action" value="1">
                <div class="modal-body p-4">
                    <input type="hidden" name="tt_id" id="ttId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Timetable Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="tt_name" placeholder="e.g. Spring 2025 Timetable" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective From Date</label>
                        <input type="date" class="form-control" name="effective_from" id="tt_eff">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Timetable</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CELL ASSIGNMENT MODAL -->
<div class="modal fade" id="cellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-square me-2"></i>Assign Class Cell</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="cellForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="cell_tt_id">
                    <input type="hidden" id="cell_class_id">
                    <input type="hidden" id="cell_period_id">
                    <input type="hidden" id="cell_day">
                    <div class="row g-3">
                        <div class="col-12">
                            <div style="background:#f8faff;border-radius:10px;padding:0.75rem 1rem;font-size:0.9rem;color:#1a3c6e;font-weight:600;" id="cellInfo"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select class="form-select" id="cell_subject">
                                <option value="">-- Select Subject --</option>
                                <?php
                                $subj2 = mysqli_query($conn, "SELECT s.*, c.Name as ClassName FROM Subject s LEFT JOIN Class c ON s.ClassID=c.ClassID ORDER BY c.Name, s.Name");
                                while($s=mysqli_fetch_assoc($subj2)): ?>
                                <option value="<?=$s['SubjectID']?>">[<?=htmlspecialchars($s['ClassName']??'')?>] <?=htmlspecialchars($s['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teacher</label>
                            <select class="form-select" id="cell_teacher">
                                <option value="">-- Select Teacher --</option>
                                <?php
                                $tch2 = mysqli_query($conn, "SELECT * FROM Teacher ORDER BY Name");
                                while($t=mysqli_fetch_assoc($tch2)): ?>
                                <option value="<?=$t['TeacherID']?>"><?=htmlspecialchars($t['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room</label>
                            <select class="form-select" id="cell_room">
                                <option value="">-- Select Room --</option>
                                <?php
                                $rooms2 = mysqli_query($conn, "SELECT * FROM Room ORDER BY Title");
                                while($r=mysqli_fetch_assoc($rooms2)): ?>
                                <option value="<?=$r['RoomID']?>"><?=htmlspecialchars($r['Title'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <select class="form-select" id="cell_section">
                                <option value="">-- Select Section --</option>
                                <?php
                                $sec2 = mysqli_query($conn, "SELECT * FROM Section ORDER BY Name");
                                while($s=mysqli_fetch_assoc($sec2)): ?>
                                <option value="<?=$s['SectionID']?>"><?=htmlspecialchars($s['Name'])?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="clearCellBtn" onclick="clearCell()" style="border-radius:10px; display:none;"><i class="fas fa-times me-1"></i>Clear Cell</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-custom" onclick="saveCell()"><i class="fas fa-save me-2"></i>Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
let currentTTID = 0;
const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

function resetTTForm() {
    document.getElementById('ttModalTitle').innerHTML = '<i class="fas fa-calendar-alt me-2"></i>New Timetable';
    document.getElementById('ttId').value    = '0';
    document.getElementById('tt_name').value = '';
    document.getElementById('tt_eff').value  = '';
}
function editTT(id, name, eff) {
    document.getElementById('ttModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Timetable';
    document.getElementById('ttId').value    = id;
    document.getElementById('tt_name').value = name;
    document.getElementById('tt_eff').value  = eff;
}
function openBuilder(ttid, name) {
    currentTTID = ttid;
    document.getElementById('builderTitle').textContent = 'Builder: ' + name;
    document.getElementById('builderSection').style.display = 'block';
    document.getElementById('filterClass').value = '';
    document.getElementById('timetableGridContainer').innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-arrow-up me-2"></i>Select a class to view timetable grid</div>';
}
function loadTimetableGrid() {
    const classId = document.getElementById('filterClass').value;
    if (!classId) return;
    fetch(`timetable_data.php?tt_id=${currentTTID}&class_id=${classId}`)
        .then(r => r.json())
        .then(data => renderGrid(data, classId))
        .catch(() => {
            document.getElementById('timetableGridContainer').innerHTML = '<div class="text-center text-muted py-4">No period data available. Add periods first.</div>';
        });
}
function renderGrid(data, classId) {
    let html = '<div class="table-responsive"><table class="timetable-grid"><thead><tr><th class="period-col">Period / Day</th>';
    DAYS.forEach(d => html += `<th class="day-header">${d}</th>`);
    html += '</tr></thead><tbody>';
    data.periods.forEach(p => {
        html += `<tr><td class="period-col"><div style="font-weight:700;">${p.Title}</div><div style="font-size:0.72rem;color:#94a3b8;">${p.StartTime} - ${p.EndTime}</div></td>`;
        DAYS.forEach(day => {
            const key = `${p.PeriodID}_${day}`;
            const cell = data.cells[key];
            if (cell) {
                html += `<td><div class="cell-filled" onclick="openCellModal(${currentTTID},${classId},${p.PeriodID},'${day}',${cell.TTCDID})">
                    <div style="font-weight:700;font-size:0.82rem;color:#1a3c6e;">${cell.SubjectName||'—'}</div>
                    <div style="font-size:0.72rem;color:#2563eb;">${cell.TeacherName||''}</div>
                    <div style="font-size:0.70rem;color:#64748b;">${cell.RoomTitle||''}</div>
                </div></td>`;
            } else {
                html += `<td><div class="cell-empty" onclick="openCellModal(${currentTTID},${classId},${p.PeriodID},'${day}',0)"><i class="fas fa-plus"></i></div></td>`;
            }
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    document.getElementById('timetableGridContainer').innerHTML = html;
}

let currentCellTTCDID = 0;
function openCellModal(ttid, classId, periodId, day, ttcdid) {
    document.getElementById('cell_tt_id').value     = ttid;
    document.getElementById('cell_class_id').value  = classId;
    document.getElementById('cell_period_id').value = periodId;
    document.getElementById('cell_day').value       = day;
    currentCellTTCDID = ttcdid;
    document.getElementById('cellInfo').textContent = `Day: ${day} | Period ID: ${periodId}`;
    document.getElementById('clearCellBtn').style.display = ttcdid > 0 ? 'block' : 'none';
    // Reset selects
    ['cell_subject','cell_teacher','cell_room','cell_section'].forEach(id => document.getElementById(id).value = '');
    new bootstrap.Modal(document.getElementById('cellModal')).show();
}
function saveCell() {
    const data = {
        tt_id:      document.getElementById('cell_tt_id').value,
        class_id:   document.getElementById('cell_class_id').value,
        period_id:  document.getElementById('cell_period_id').value,
        day:        document.getElementById('cell_day').value,
        subject_id: document.getElementById('cell_subject').value,
        teacher_id: document.getElementById('cell_teacher').value,
        room_id:    document.getElementById('cell_room').value,
        section_id: document.getElementById('cell_section').value,
    };
    fetch('save_cell.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) })
        .then(r => r.json())
        .then(res => {
            if (res.success) { bootstrap.Modal.getInstance(document.getElementById('cellModal')).hide(); loadTimetableGrid(); }
            else alert('Error: ' + res.message);
        });
}
function clearCell() {
    if (!confirm('Clear this cell?')) return;
    fetch('save_cell.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ clear: true, ttcdid: currentCellTTCDID }) })
        .then(r => r.json())
        .then(res => { bootstrap.Modal.getInstance(document.getElementById('cellModal')).hide(); loadTimetableGrid(); });
}
</script>
</body>
</html>
