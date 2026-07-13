<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Period WHERE PeriodID=$id");
    $success = "Period deleted.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = (int)($_POST['period_id'] ?? 0);
    $title    = mysqli_real_escape_string($conn, trim($_POST['title']));
    $number   = (int)$_POST['number'];
    $start    = $_POST['start_time'];
    $end      = $_POST['end_time'];
    $fri_start= $_POST['fri_start'];
    $fri_end  = $_POST['fri_end'];
    $is_zero  = isset($_POST['is_zero'])  ? 1 : 0;
    $is_break = isset($_POST['is_break']) ? 1 : 0;
    $dispord  = (int)$_POST['display_order'];

    if (empty($title)) { $error = "Period title is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Period SET Title='$title',Number=$number,StartTime='$start',EndTime='$end',FridayStartTime='$fri_start',FridayEndTime='$fri_end',IsZeroPeriod=$is_zero,IsBreak=$is_break,DisplayOrder=$dispord WHERE PeriodID=$id")
                ? $success = "Period updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Period (Title,Number,StartTime,EndTime,FridayStartTime,FridayEndTime,IsZeroPeriod,IsBreak,DisplayOrder) VALUES ('$title',$number,'$start','$end','$fri_start','$fri_end',$is_zero,$is_break,$dispord)")
                ? $success = "Period added." : $error = mysqli_error($conn);
        }
    }
}

$list = mysqli_query($conn, "SELECT * FROM Period ORDER BY DisplayOrder, Number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Periods - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Periods</div>
            <h4><i class="fas fa-clock me-2"></i>Periods</h4>
            <p>Manage class periods and break times</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#periodModal" onclick="resetPeriodForm()">
            <i class="fas fa-plus me-2"></i>Add Period
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header"><div class="data-table-title"><i class="fas fa-list me-2"></i>All Periods</div></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Period</th><th>No.</th><th>Start – End</th><th>Friday Timing</th><th>Type</th><th>Order</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($list)==0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-clock fa-2x mb-2 d-block"></i>No periods found</td></tr>
                <?php else: $i=1; while($row=mysqli_fetch_assoc($list)): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if($row['IsBreak']): ?>
                                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#fcd34d);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;">
                                    <i class="fas fa-coffee"></i>
                                </div>
                            <?php else: ?>
                                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#1a3c6e,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;">
                                    <?=$row['Number']?>
                                </div>
                            <?php endif; ?>
                            <strong><?=htmlspecialchars($row['Title'])?></strong>
                        </div>
                    </td>
                    <td><?=$row['Number']?></td>
                    <td>
                        <span style="font-weight:600;color:#1e293b;"><?=date('h:i A', strtotime($row['StartTime']))?></span>
                        <span class="text-muted mx-1">→</span>
                        <span style="font-weight:600;color:#1e293b;"><?=date('h:i A', strtotime($row['EndTime']))?></span>
                    </td>
                    <td>
                        <?php if($row['FridayStartTime']): ?>
                            <span style="font-size:0.82rem;"><?=date('h:i A', strtotime($row['FridayStartTime']))?> → <?=date('h:i A', strtotime($row['FridayEndTime']))?></span>
                        <?php else: echo '<span class="text-muted">Same</span>'; endif; ?>
                    </td>
                    <td>
                        <?php if($row['IsBreak']): ?>
                            <span style="background:#fffbeb;color:#d97706;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;"><i class="fas fa-coffee me-1"></i>Break</span>
                        <?php elseif($row['IsZeroPeriod']): ?>
                            <span style="background:#f5f3ff;color:#7c3aed;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">Zero Period</span>
                        <?php else: ?>
                            <span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:50px;font-size:0.78rem;font-weight:600;">Regular</span>
                        <?php endif; ?>
                    </td>
                    <td><span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:50px;font-size:0.8rem;font-weight:600;"><?=$row['DisplayOrder']?></span></td>
                    <td>
                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#periodModal"
                            onclick="editPeriod(<?=htmlspecialchars(json_encode($row))?>)"><i class="fas fa-edit"></i></button>
                        <a href="?delete=<?=$row['PeriodID']?>" class="btn-action btn-delete ms-1" onclick="return confirm('Delete this period?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="periodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="periModalTitle"><i class="fas fa-clock me-2"></i>Add Period</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="period_id" id="periodId" value="0">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Period Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="p_title" placeholder="e.g. Period 1, Lunch Break" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Period Number</label>
                            <input type="number" class="form-control" name="number" id="p_number" value="1" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" id="p_start">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" id="p_end">
                        </div>
                        <div class="col-12"><div style="border-top:1px dashed #e5e7eb;margin:0.5rem 0;"></div><p style="font-size:0.82rem;color:#94a3b8;margin:0 0 0.5rem;"><i class="fas fa-info-circle me-1"></i>Friday timings (leave blank to use regular times)</p></div>
                        <div class="col-md-6">
                            <label class="form-label">Friday Start Time</label>
                            <input type="time" class="form-control" name="fri_start" id="p_fri_start">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Friday End Time</label>
                            <input type="time" class="form-control" name="fri_end" id="p_fri_end">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" id="p_order" value="1" min="1">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_zero" id="p_zero">
                                <label class="form-check-label fw-600" for="p_zero">Zero Period</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_break" id="p_break">
                                <label class="form-check-label fw-600" for="p_break">Break / Lunch</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetPeriodForm() {
    document.getElementById('periModalTitle').innerHTML = '<i class="fas fa-clock me-2"></i>Add Period';
    document.getElementById('periodId').value = '0';
    ['p_title','p_start','p_end','p_fri_start','p_fri_end'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('p_number').value = '1';
    document.getElementById('p_order').value  = '1';
    document.getElementById('p_zero').checked  = false;
    document.getElementById('p_break').checked = false;
}
function editPeriod(d) {
    document.getElementById('periModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Period';
    document.getElementById('periodId').value    = d.PeriodID;
    document.getElementById('p_title').value     = d.Title;
    document.getElementById('p_number').value    = d.Number;
    document.getElementById('p_start').value     = d.StartTime    ? d.StartTime.substring(0,5)    : '';
    document.getElementById('p_end').value       = d.EndTime      ? d.EndTime.substring(0,5)      : '';
    document.getElementById('p_fri_start').value = d.FridayStartTime ? d.FridayStartTime.substring(0,5) : '';
    document.getElementById('p_fri_end').value   = d.FridayEndTime   ? d.FridayEndTime.substring(0,5)   : '';
    document.getElementById('p_order').value     = d.DisplayOrder;
    document.getElementById('p_zero').checked    = d.IsZeroPeriod == 1;
    document.getElementById('p_break').checked   = d.IsBreak == 1;
}
</script>
</body>
</html>
