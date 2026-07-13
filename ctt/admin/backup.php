<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

$success = $error = '';
$backup_dir = '../database/backups/';

// Create backup folder if not exists
if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);

// DOWNLOAD BACKUP
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $path = $backup_dir . $file;
    if (file_exists($path) && pathinfo($path, PATHINFO_EXTENSION) == 'sql') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// DELETE BACKUP
if (isset($_GET['del'])) {
    $file = basename($_GET['del']);
    $path = $backup_dir . $file;
    if (file_exists($path)) { unlink($path); $success = "Backup file deleted."; }
}

// CREATE BACKUP
if (($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_backup'])) || isset($_GET['auto'])) {
    $filename = 'ctt_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backup_dir . $filename;

    $tables_query = mysqli_query($conn, "SHOW TABLES");
    $sql_dump = "-- CTT Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n-- Database: ctt_db\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

    while ($table_row = mysqli_fetch_row($tables_query)) {
        $table = $table_row[0];

        // DROP + CREATE structure
        $create_res = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE `$table`"));
        $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n" . $create_res[1] . ";\n\n";

        // Data
        $rows = mysqli_query($conn, "SELECT * FROM `$table`");
        $num_cols = mysqli_num_fields($rows);

        if (mysqli_num_rows($rows) > 0) {
            $sql_dump .= "INSERT INTO `$table` VALUES\n";
            $row_strs = [];
            while ($row = mysqli_fetch_row($rows)) {
                $vals = array_map(function($v) use ($conn) {
                    return is_null($v) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $v) . "'";
                }, $row);
                $row_strs[] = '(' . implode(',', $vals) . ')';
            }
            $sql_dump .= implode(",\n", $row_strs) . ";\n\n";
        }
    }
    $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

    if (file_put_contents($filepath, $sql_dump)) {
        $success = "Backup created: <strong>$filename</strong>";
    } else {
        $error = "Failed to create backup. Check folder permissions.";
    }
}

// RESTORE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restore_file'])) {
    $file = basename($_POST['restore_file']);
    $path = $backup_dir . $file;
    if (file_exists($path)) {
        $sql = file_get_contents($path);
        mysqli_multi_query($conn, $sql);
        $success = "Database restored from <strong>$file</strong> successfully.";
    } else {
        $error = "Backup file not found.";
    }
}

// List backups
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.sql');
    foreach ($files as $f) {
        $backups[] = ['name' => basename($f), 'size' => filesize($f), 'date' => filemtime($f)];
    }
    usort($backups, fn($a,$b) => $b['date'] - $a['date']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - CTT</title>
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
            <div class="breadcrumb-custom"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> Backup</div>
            <h4><i class="fas fa-database me-2"></i>Database Backup & Restore</h4>
            <p>Create, download, and restore database backups</p>
        </div>
        <form method="POST">
            <button type="submit" name="create_backup" value="1" class="btn-primary-custom" onclick="return confirm('Create a new backup now?')">
                <i class="fas fa-download me-2"></i>Create Backup Now
            </button>
        </form>
    </div>

    <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i><?=$success?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;"><i class="fas fa-exclamation-circle me-2"></i><?=$error?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="row g-4">
        <!-- Backup Info -->
        <div class="col-lg-4">
            <div class="form-card">
                <h6 style="font-weight:800;color:#1a3c6e;margin-bottom:1.5rem;"><i class="fas fa-info-circle me-2 text-primary"></i>Backup Information</h6>
                <div style="background:#f8faff;border-radius:12px;padding:1.2rem;margin-bottom:1rem;">
                    <div style="font-size:0.82rem;color:#64748b;margin-bottom:0.3rem;">Database Name</div>
                    <div style="font-weight:700;color:#1a3c6e;">ctt_db</div>
                </div>
                <div style="background:#f8faff;border-radius:12px;padding:1.2rem;margin-bottom:1rem;">
                    <div style="font-size:0.82rem;color:#64748b;margin-bottom:0.3rem;">Total Backups</div>
                    <div style="font-weight:700;color:#1a3c6e;"><?=count($backups)?> files</div>
                </div>
                <div style="background:#f8faff;border-radius:12px;padding:1.2rem;margin-bottom:1.5rem;">
                    <div style="font-size:0.82rem;color:#64748b;margin-bottom:0.3rem;">Last Backup</div>
                    <div style="font-weight:700;color:#1a3c6e;"><?= !empty($backups) ? date('M d, Y H:i', $backups[0]['date']) : 'Never' ?></div>
                </div>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:1rem;">
                    <p style="font-size:0.82rem;color:#92400e;margin:0;"><i class="fas fa-exclamation-triangle me-1"></i><strong>Important:</strong> Always create a backup before making major changes to your system data.</p>
                </div>
            </div>
        </div>

        <!-- Backup List -->
        <div class="col-lg-8">
            <div class="data-table-card">
                <div class="data-table-header"><div class="data-table-title"><i class="fas fa-history me-2"></i>Backup History</div></div>
                <?php if (empty($backups)): ?>
                    <div class="text-center text-muted py-5"><i class="fas fa-database fa-3x mb-3 d-block"></i><h6>No backups yet</h6><p>Click "Create Backup Now" to create your first backup.</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>#</th><th>Filename</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($backups as $i => $b): ?>
                        <tr>
                            <td><?=$i+1?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#1a3c6e,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;"><i class="fas fa-file-code"></i></div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.88rem;"><?=htmlspecialchars($b['name'])?></div>
                                        <div style="font-size:0.75rem;color:#94a3b8;">SQL Dump File</div>
                                    </div>
                                </div>
                            </td>
                            <td><?=round($b['size']/1024, 1)?> KB</td>
                            <td><?=date('M d, Y H:i', $b['date'])?></td>
                            <td>
                                <a href="?download=<?=urlencode($b['name'])?>" class="btn-action btn-view" title="Download"><i class="fas fa-download"></i></a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Restore database from this backup? Current data will be overwritten!')">
                                    <input type="hidden" name="restore_file" value="<?=htmlspecialchars($b['name'])?>">
                                    <button type="submit" class="btn-action btn-edit ms-1" title="Restore" style="background:#fff3cd;color:#856404;"><i class="fas fa-undo"></i></button>
                                </form>
                                <a href="?del=<?=urlencode($b['name'])?>" class="btn-action btn-delete ms-1" title="Delete" onclick="return confirm('Delete this backup file?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body>
</html>
