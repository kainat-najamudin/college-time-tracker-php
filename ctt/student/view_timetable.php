<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('student');

$teacher_id = $_SESSION['user_id'];
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Name FROM Teacher WHERE TeacherID = $teacher_id"));

// Get all periods
$periods = mysqli_query($conn, "SELECT * FROM period ORDER BY DisplayOrder");

// Days
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Weekly Timetable - CTT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/ctt/assets/css/layout.css" rel="stylesheet">

    <style>
        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .timetable-grid th {
            background: #1e40af;
            color: #fff;
            padding: 12px 8px;
            text-align: center;
            font-weight: 700;
            border: 1px solid #2563eb;
        }
        .timetable-grid td {
            padding: 10px 6px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
            min-width: 130px;
        }
        .period-col {
            background: #f8fafc;
            font-weight: 700;
            color: #1e40af;
            min-width: 110px;
        }
        .cell-filled {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 8px;
            padding: 8px 6px;
            border: 1px solid #bfdbfe;
            height: 100%;
        }
        .cell-empty {
            background: #f8fafc;
            color: #94a3b8;
            border-radius: 8px;
            padding: 20px 6px;
            font-size: 0.8rem;
        }
        .subject-name { font-weight: 700; color: #1e40af; font-size: 0.9rem; }
        .section-name { font-size: 0.78rem; color: #2563eb; }
        .room-name { font-size: 0.75rem; color: #64748b; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_teacher.php'; ?>

<main class="main-content" id="mainContent">

    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-calendar-week me-2"></i> My Weekly Timetable</h4>
                <p class="text-muted">Showing all classes & sections assigned to <?= htmlspecialchars($teacher['Name']) ?></p>
            </div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i> Print Timetable
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table timetable-grid text-center">
                    <thead>
                        <tr>
                            <th class="period-col">Period / Time</th>
                            <?php foreach($days as $day): ?>
                                <th><?= $day ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    mysqli_data_seek($periods, 0);
                    while($period = mysqli_fetch_assoc($periods)):
                    ?>
                        <tr>
                            <td class="period-col">
                                <div><?= htmlspecialchars($period['Title']) ?></div>
                                <div style="font-size:0.75rem;color:#64748b;">
                                    <?= $period['StartTime'] ?> - <?= $period['EndTime'] ?>
                                </div>
                            </td>
                            <?php foreach($days as $day): ?>
                                <td>
                                    <?php
                                    $query = mysqli_query($conn, 
                                        "SELECT 
                                            sub.Name as SubjectName,
                                            c.Name as ClassName,
                                            sec.Name as SectionName,
                                            r.Title as RoomTitle
                                         FROM timetablecelldetail tcd
                                         JOIN timetablecell tc ON tcd.TTCID = tc.TTCID
                                         JOIN subject sub ON tcd.SubjectID = sub.SubjectID
                                         LEFT JOIN class c ON tc.ClassID = c.ClassID
                                         LEFT JOIN section sec ON tcd.SectionID = sec.SectionID
                                         LEFT JOIN room r ON tcd.RoomID = r.RoomID
                                         WHERE tcd.TeacherID = $teacher_id 
                                           AND tcd.Days = '$day'
                                           AND tc.PeriodID = {$period['PeriodID']}
                                         LIMIT 1");

                                    if(mysqli_num_rows($query) > 0):
                                        $cell = mysqli_fetch_assoc($query);
                                    ?>
                                        <div class="cell-filled">
                                            <div class="subject-name"><?= htmlspecialchars($cell['SubjectName']) ?></div>
                                            <div class="section-name">
                                                <?= htmlspecialchars($cell['ClassName'] ?? '') ?> 
                                                <?= $cell['SectionName'] ? '- ' . htmlspecialchars($cell['SectionName']) : '' ?>
                                            </div>
                                            <?php if($cell['RoomTitle']): ?>
                                            <div class="room-name"><i class="fas fa-door-open"></i> <?= htmlspecialchars($cell['RoomTitle']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="cell-empty">—</div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/ctt/assets/js/layout.js"></script>
</body>
</html>