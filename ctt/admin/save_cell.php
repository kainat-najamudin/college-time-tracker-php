<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireRole('admin');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// CLEAR CELL
if (!empty($input['clear'])) {
    $ttcdid = (int)$input['ttcdid'];
    mysqli_query($conn, "DELETE FROM TimetableCellDetail WHERE TTCDID=$ttcdid");
    echo json_encode(['success' => true]);
    exit;
}

$tt_id      = (int)$input['tt_id'];
$class_id   = (int)$input['class_id'];
$period_id  = (int)$input['period_id'];
$day        = mysqli_real_escape_string($conn, $input['day']);
$subject_id = (int)$input['subject_id'];
$teacher_id = (int)$input['teacher_id'];
$room_id    = (int)$input['room_id'];
$section_id = (int)$input['section_id'];

// Get or create TimetableCell
$ttc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT TTCID FROM TimetableCell WHERE TTID=$tt_id AND ClassID=$class_id AND PeriodID=$period_id"));
if ($ttc) {
    $ttcid = $ttc['TTCID'];
} else {
    mysqli_query($conn, "INSERT INTO TimetableCell (TTID, ClassID, PeriodID) VALUES ($tt_id, $class_id, $period_id)");
    $ttcid = mysqli_insert_id($conn);
}

// Conflict check: same teacher, same period, same day
if ($teacher_id > 0) {
    $conflict = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT tcd.TTCDID FROM TimetableCellDetail tcd
         JOIN TimetableCell tc ON tc.TTCID = tcd.TTCID
         WHERE tcd.TeacherID=$teacher_id AND tcd.Days='$day' AND tc.PeriodID=$period_id AND tc.ClassID != $class_id"
    ));
    if ($conflict) {
        echo json_encode(['success' => false, 'message' => 'Conflict: This teacher is already assigned to another class at this period/day.']);
        exit;
    }
}

// Check room conflict
if ($room_id > 0) {
    $rconflict = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT tcd.TTCDID FROM TimetableCellDetail tcd
         JOIN TimetableCell tc ON tc.TTCID = tcd.TTCID
         WHERE tcd.RoomID=$room_id AND tcd.Days='$day' AND tc.PeriodID=$period_id AND tc.ClassID != $class_id"
    ));
    if ($rconflict) {
        echo json_encode(['success' => false, 'message' => 'Conflict: This room is already booked for another class at this period/day.']);
        exit;
    }
}

// Delete existing and insert new
mysqli_query($conn, "DELETE FROM TimetableCellDetail WHERE TTCID=$ttcid AND Days='$day'");
mysqli_query($conn, "INSERT INTO TimetableCellDetail (TTCID, SubjectID, TeacherID, SectionID, RoomID, Days, DisplayOrder)
                     VALUES ($ttcid, $subject_id, $teacher_id, $section_id, $room_id, '$day', 1)");

// Update LastUpdatedOn
mysqli_query($conn, "UPDATE Timetable SET LastUpdatedOn=NOW() WHERE TTID=$tt_id");

echo json_encode(['success' => true]);
