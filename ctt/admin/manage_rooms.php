<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Room WHERE RoomID=$id");
    $success = "Room deleted.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id      = (int)($_POST['room_id'] ?? 0);
    $title   = mysqli_real_escape_string($conn, trim($_POST['title']));
    $tname   = mysqli_real_escape_string($conn, trim($_POST['ttdname']));

    if (empty($title)) { $error = "Room title is required."; }
    else {
        if ($id > 0) {
            mysqli_query($conn, "UPDATE Room SET Title='$title', TTDName='$tname' WHERE RoomID=$id")
                ? $success = "Room updated." : $error = mysqli_error($conn);
        } else {
            mysqli_query($conn, "INSERT INTO Room (Title, TTDName) VALUES ('$title','$tname')")
                ? $success = "Room added." : $error = mysqli_error($conn);
        }
    }
}

$list = mysqli_query($conn, "SELECT * FROM Room ORDER BY RoomID DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Rooms</div>
            <h4><i class="fas fa-door-open me-2"></i>Rooms</h4>
            <p>Manage classrooms and labs</p>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="resetRoomForm()">
            <i class="fas fa-plus me-2"></i>Add Room
        </button>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="row g-3 mb-4" id="roomCards">
        <?php
        $list2 = mysqli_query($conn, "SELECT * FROM Room ORDER BY RoomID DESC");
        while($r=mysqli_fetch_assoc($list2)):
        ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div style="background:#fff;border-radius:16px;padding:1.5rem;border:1px solid #f1f5f9;box-shadow:0 2px 10px rgba(0,0,0,0.05);transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#0284c7,#38bdf8);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#roomModal"
                            onclick="editRoom(<?=$r['RoomID']?>,'<?=addslashes($r['Title'])?>','<?=addslashes($r['TTDName']??'')?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?=$r['RoomID']?>" class="btn-action btn-delete" onclick="return confirm('Delete this room?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <div style="font-weight:700;font-size:1rem;color:#1e293b;"><?=htmlspecialchars($r['Title'])?></div>
                <?php if($r['TTDName']): ?><div style="font-size:0.8rem;color:#94a3b8;margin-top:0.25rem;"><?=htmlspecialchars($r['TTDName'])?></div><?php endif; ?>
                <div style="margin-top:0.75rem;font-size:0.78rem;color:#64748b;"><i class="fas fa-hashtag me-1"></i>Room ID: <?=$r['RoomID']?></div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($list)==0): ?>
            <div class="col-12"><div class="text-center text-muted py-5"><i class="fas fa-door-open fa-3x mb-3 d-block"></i>No rooms found. Add your first room.</div></div>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="roomModalTitle"><i class="fas fa-door-open me-2"></i>Add Room</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="room_id" id="roomId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Room Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="r_title" placeholder="e.g. Room 101, Lab A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">TTD Name <small class="text-muted">(Timetable Display Name)</small></label>
                        <input type="text" class="form-control" name="ttdname" id="r_tname" placeholder="Short display name for timetable">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save me-2"></i>Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
<script>
function resetRoomForm() {
    document.getElementById('roomModalTitle').innerHTML = '<i class="fas fa-door-open me-2"></i>Add Room';
    document.getElementById('roomId').value  = '0';
    document.getElementById('r_title').value = '';
    document.getElementById('r_tname').value = '';
}
function editRoom(id, title, tname) {
    document.getElementById('roomModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Room';
    document.getElementById('roomId').value  = id;
    document.getElementById('r_title').value = title;
    document.getElementById('r_tname').value = tname;
}
</script>
</body>
</html>
