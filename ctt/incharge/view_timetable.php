<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('incharge');

$timetables = mysqli_query($conn,"SELECT tt.*, (SELECT COUNT(*) FROM TimetableCell tc WHERE tc.TTID=tt.TTID) as CellCount FROM Timetable tt ORDER BY tt.TTID DESC");
$classes    = mysqli_query($conn,"SELECT * FROM Class WHERE IsActive=1 ORDER BY Name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetables - CTT In-Charge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">
    <style>
        .tt-view-card { background:#fff;border-radius:16px;padding:1.5rem;border:1px solid #f1f5f9;box-shadow:0 2px 10px rgba(0,0,0,0.05);transition:all 0.3s; }
        .tt-view-card:hover { transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,0.08); }
        .tt-icon { width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#1a3c6e,#2563eb);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;margin-bottom:1rem; }
        .timetable-grid { width:100%;border-collapse:collapse;font-size:0.8rem; }
        .timetable-grid th { background:#1a3c6e;color:#fff;padding:10px 8px;text-align:center;font-weight:700;border:1px solid #2563eb; }
        .timetable-grid td { padding:6px;border:1px solid #e5e7eb;text-align:center;vertical-align:middle;min-width:110px; }
        .timetable-grid .period-col { background:#f8faff;font-weight:700;color:#1a3c6e;min-width:90px; }
        .cell-filled { background:linear-gradient(135deg,#eff6ff,#e0f2fe);border-radius:8px;padding:6px 4px;border:1px solid #bfdbfe; }
        .cell-empty  { background:#fafafa;border-radius:8px;padding:8px;color:#e2e8f0;font-size:0.75rem; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_incharge.php'; ?>

<main class="main-content" id="mainContent">
    <div class="page-header">
        <div class="page-title">
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Timetables</div>
            <h4><i class="fas fa-calendar-alt me-2"></i>View Timetables</h4>
            <p>Browse and view academic timetables by class</p>
        </div>
    </div>

    <!-- Timetable Cards -->
    <div class="row g-3 mb-4">
    <?php
    $tt_arr = [];
    while($tt=mysqli_fetch_assoc($timetables)) $tt_arr[]=$tt;
    if(empty($tt_arr)): ?>
        <div class="col-12">
            <div class="text-center py-5" style="background:#fff;border-radius:16px;border:2px dashed #e5e7eb;">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No timetables available</h5>
                <p class="text-muted">Admin hasn't created any timetables yet.</p>
            </div>
        </div>
    <?php else: foreach($tt_arr as $tt): ?>
    <div class="col-xl-4 col-md-6">
        <div class="tt-view-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="tt-icon"><i class="fas fa-calendar-alt"></i></div>
                <button class="btn btn-sm" style="background:#eff6ff;color:#2563eb;border-radius:10px;font-size:0.82rem;font-weight:600;"
                    onclick="openViewer(<?=$tt['TTID']?>,'<?=addslashes($tt['Name'])?>');document.getElementById('viewerSection').scrollIntoView({behavior:'smooth'})">
                    <i class="fas fa-eye me-1"></i>View Grid
                </button>
            </div>
            <h5 style="font-weight:800;color:#1e293b;margin-top:0.5rem;"><?=htmlspecialchars($tt['Name'])?></h5>
            <div class="d-flex gap-3 mt-2">
                <span style="font-size:0.82rem;color:#64748b;"><i class="fas fa-table me-1"></i><?=$tt['CellCount']?> cells</span>
                <?php if($tt['WithEffectiveFrom']): ?>
                <span style="font-size:0.82rem;color:#64748b;"><i class="fas fa-calendar me-1"></i><?=date('M d, Y',strtotime($tt['WithEffectiveFrom']))?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
    </div>

    <!-- Timetable Grid Viewer -->
    <div id="viewerSection" style="display:none;">
        <div class="form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 style="font-weight:800;color:#1a3c6e;margin:0;" id="viewerTitle">Timetable Viewer</h5>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">Select a class to view its timetable</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select" id="viewClass" style="width:220px;" onchange="loadGrid()">
                        <option value="">Select Class</option>
                        <?php while($c=mysqli_fetch_assoc($classes)): ?>
                        <option value="<?=$c['ClassID']?>"><?=htmlspecialchars($c['Name'])?></option>
                        <?php endwhile; ?>
                    </select>
                    <button class="btn btn-light" onclick="document.getElementById('viewerSection').style.display='none'" style="border-radius:10px;"><i class="fas fa-times me-1"></i>Close</button>
                    <button class="btn btn-sm" style="background:#eff6ff;color:#2563eb;border-radius:10px;" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
                </div>
            </div>
            <div id="gridContainer">
                <div class="text-center text-muted py-4"><i class="fas fa-arrow-up me-2"></i>Select a class above to load timetable</div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
let currentTTID = 0;
const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

function openViewer(ttid, name) {
    currentTTID = ttid;
    document.getElementById('viewerTitle').textContent = 'Viewing: ' + name;
    document.getElementById('viewerSection').style.display = 'block';
    document.getElementById('viewClass').value = '';
    document.getElementById('gridContainer').innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-arrow-up me-2"></i>Select a class above to load timetable</div>';
}
function loadGrid() {
    const classId = document.getElementById('viewClass').value;
    if (!classId) return;
    document.getElementById('gridContainer').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    fetch(`/ctt/admin/timetable_data.php?tt_id=${currentTTID}&class_id=${classId}`)
        .then(r => r.json())
        .then(data => renderGrid(data));
}
function renderGrid(data) {
    if (!data.periods || data.periods.length === 0) {
        document.getElementById('gridContainer').innerHTML = '<div class="text-center text-muted py-4">No periods configured yet.</div>';
        return;
    }
    let html = '<div class="table-responsive"><table class="timetable-grid"><thead><tr><th class="period-col">Period / Day</th>';
    DAYS.forEach(d => html += `<th style="font-size:0.82rem;font-weight:700;">${d}</th>`);
    html += '</tr></thead><tbody>';
    data.periods.forEach(p => {
        html += `<tr><td class="period-col"><div style="font-weight:700;">${p.Title}</div><div style="font-size:0.72rem;color:#94a3b8;">${p.StartTime} - ${p.EndTime}</div></td>`;
        DAYS.forEach(day => {
            const key = `${p.PeriodID}_${day}`;
            const cell = data.cells[key];
            if (cell) {
                html += `<td><div class="cell-filled">
                    <div style="font-weight:700;font-size:0.82rem;color:#1a3c6e;">${cell.SubjectName||'—'}</div>
                    <div style="font-size:0.72rem;color:#2563eb;">${cell.TeacherName||''}</div>
                    <div style="font-size:0.70rem;color:#64748b;">${cell.RoomTitle||''}</div>
                </div></td>`;
            } else {
                html += `<td><div class="cell-empty">—</div></td>`;
            }
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    document.getElementById('gridContainer').innerHTML = html;
}
</script>
</body>
</html>
