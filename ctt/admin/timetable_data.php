<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

header('Content-Type: application/json');

$tt_id    = (int)($_GET['tt_id']    ?? 0);
$class_id = (int)($_GET['class_id'] ?? 0);

// Get periods
$periods = [];
$q = mysqli_query($conn, "SELECT PeriodID, Title, TIME_FORMAT(StartTime,'%h:%i %p') as StartTime, TIME_FORMAT(EndTime,'%h:%i %p') as EndTime FROM Period WHERE IsBreak=0 ORDER BY DisplayOrder");
while ($r = mysqli_fetch_assoc($q)) $periods[] = $r;

// Get assigned cells
$cells = [];
$q2 = mysqli_query($conn, "
    SELECT tc.PeriodID, tcd.Days as Day, tcd.TTCDID,
           s.Name as SubjectName, t.Name as TeacherName, r.Title as RoomTitle
    FROM TimetableCell tc
    JOIN TimetableCellDetail tcd ON tcd.TTCID = tc.TTCID
    LEFT JOIN Subject s  ON s.SubjectID   = tcd.SubjectID
    LEFT JOIN Teacher t  ON t.TeacherID   = tcd.TeacherID
    LEFT JOIN Room r     ON r.RoomID      = tcd.RoomID
    WHERE tc.TTID = $tt_id AND tc.ClassID = $class_id
");
while ($r = mysqli_fetch_assoc($q2)) {
    $key = $r['PeriodID'] . '_' . $r['Day'];
    $cells[$key] = $r;
}

echo json_encode(['periods' => $periods, 'cells' => $cells]);
