<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');

// ───────────────────────────── POST handler ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = $_POST['entity'] ?? '';
    $action = $_POST['action'] ?? 'create';

    // ── SECTION (separate handler) ───────────────────────────────────────
    if ($entity === 'section' || isset($_POST['sec_action'])) {
        $id      = (int)($_POST['sec_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $ttdname = trim($_POST['ttdname'] ?? '');
        $sem     = trim($_POST['semester'] ?? '');
        $order   = (int)($_POST['display_order'] ?? 0);
        $subjid  = (int)($_POST['subject_id'] ?? 0);
        $tchid   = (int)($_POST['teacher_id'] ?? 0);

        if (empty($name)) {
            add_flash('error', 'Section name is required.');
        } else {
            $name_s    = mysqli_real_escape_string($conn, $name);
            $ttdname_s = mysqli_real_escape_string($conn, $ttdname);
            $sem_s     = mysqli_real_escape_string($conn, $sem);
            $subj_val  = $subjid ?: 'NULL';
            $tch_val   = $tchid  ?: 'NULL';

            if ($id > 0) {
                $ok = mysqli_query($conn, "UPDATE section SET Name='$name_s', TTDName='$ttdname_s', Semester='$sem_s', DisplayOrder=$order, SubjectID=$subj_val, TeacherID=$tch_val WHERE SectionID=$id");
                $ok ? add_flash('success', 'Section updated successfully.') : add_flash('error', mysqli_error($conn));
            } else {
                $ok = mysqli_query($conn, "INSERT INTO section (Name,TTDName,Semester,DisplayOrder,IsActive,SubjectID,TeacherID) VALUES ('$name_s','$ttdname_s','$sem_s',$order,1,$subj_val,$tch_val)");
                $ok ? add_flash('success', 'Section added successfully.') : add_flash('error', mysqli_error($conn));
            }
        }
        redirect_to('admin/entities.php?tab=sections');
    }

    $stmt = null;

    // ── DELETE ──────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id  = (int)($_POST['record_id'] ?? 0);
        $map = [
            'program' => ['program', 'ProgramID'],
            'class'   => ['class',   'ClassID'],
            'subject' => ['subject', 'SubjectID'],
            'room'    => ['room',    'RoomID'],
            'period'  => ['period',  'PeriodID'],
        ];
        if (isset($map[$entity])) {
            [$tbl, $col] = $map[$entity];
            $stmt = mysqli_prepare($conn, "DELETE FROM `$tbl` WHERE `$col` = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
        }

    // ── UPDATE ──────────────────────────────────────────────────────────
    } elseif ($action === 'update') {
        $id = (int)($_POST['record_id'] ?? 0);

        if ($entity === 'program') {
            $name = trim($_POST['program_name'] ?? '');
            $type = trim($_POST['academic_type'] ?? '');
            $stmt = mysqli_prepare($conn, 'UPDATE program SET Name=?, AcademicSystemType=? WHERE ProgramID=?');
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $type, $id);

        } elseif ($entity === 'class') {
            $name    = trim($_POST['class_name']      ?? '');
            $session = trim($_POST['session']         ?? '');
            $ttd     = trim($_POST['class_ttd']       ?? '');
            $sem     = trim($_POST['class_semester']  ?? '');
            $program = (int)($_POST['program_id']     ?? 0);
            $dates   = trim($_POST['important_dates'] ?? '');
            $color   = trim($_POST['row_color']       ?: '#ffffff');
            $stmt    = mysqli_prepare($conn, 'UPDATE `class` SET Name=?,Session=?,TTDName=?,CurrentSemester=?,ProgramID=?,ImportantDates=?,RowColor=? WHERE ClassID=?');
            mysqli_stmt_bind_param($stmt, 'ssssissi', $name, $session, $ttd, $sem, $program, $dates, $color, $id);

        } elseif ($entity === 'subject') {
            $class = (int)($_POST['subject_class_id'] ?? 0);
            $name  = trim($_POST['subject_name']      ?? '');
            $sem   = trim($_POST['subject_semester']  ?? '');
            $order = (int)($_POST['subject_order']    ?? 1);
            $stmt  = mysqli_prepare($conn, 'UPDATE subject SET ClassID=?,Name=?,Semester=?,DisplayOrder=? WHERE SubjectID=?');
            mysqli_stmt_bind_param($stmt, 'issii', $class, $name, $sem, $order, $id);

        } elseif ($entity === 'room') {
            $title = trim($_POST['room_title'] ?? '');
            $ttd   = trim($_POST['room_ttd']   ?? '');
            $stmt  = mysqli_prepare($conn, 'UPDATE room SET Title=?,TTDName=? WHERE RoomID=?');
            mysqli_stmt_bind_param($stmt, 'ssi', $title, $ttd, $id);

        } elseif ($entity === 'period') {
            $title = trim($_POST['period_title']  ?? '');
            $num   = (int)($_POST['period_number'] ?? 0);
            $st    = $_POST['start']  ?? null;
            $et    = $_POST['end']    ?? null;
            $fst   = $_POST['fstart'] ?? null;
            $fet   = $_POST['fend']   ?? null;
            $order = (int)($_POST['period_order'] ?? 1);
            $stmt  = mysqli_prepare($conn, 'UPDATE period SET Title=?,Number=?,StartTime=?,EndTime=?,FridayStartTime=?,FridayEndTime=?,DisplayOrder=? WHERE PeriodID=?');
            mysqli_stmt_bind_param($stmt, 'sissssii', $title, $num, $st, $et, $fst, $fet, $order, $id);
        }

    // ── CREATE ──────────────────────────────────────────────────────────
    } else {
        if ($entity === 'program') {
            $name = trim($_POST['program_name'] ?? '');
            $type = trim($_POST['academic_type'] ?? '');
            $stmt = mysqli_prepare($conn, 'INSERT INTO program (Name,AcademicSystemType) VALUES (?,?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $type);

        } elseif ($entity === 'class') {
            $name    = trim($_POST['class_name']      ?? '');
            $session = trim($_POST['session']         ?? '');
            $ttd     = trim($_POST['class_ttd']       ?? '');
            $sem     = trim($_POST['class_semester']  ?? '');
            $program = (int)($_POST['program_id']     ?? 0);
            $dates   = trim($_POST['important_dates'] ?? '');
            $color   = trim($_POST['row_color']       ?: '#ffffff');
            $stmt    = mysqli_prepare($conn, 'INSERT INTO `class` (Name,Session,TTDName,CurrentSemester,ProgramID,ImportantDates,RowColor) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'ssssiss', $name, $session, $ttd, $sem, $program, $dates, $color);

        } elseif ($entity === 'subject') {
            $class = (int)($_POST['subject_class_id'] ?? 0);
            $name  = trim($_POST['subject_name']      ?? '');
            $sem   = trim($_POST['subject_semester']  ?? '');
            $order = (int)($_POST['subject_order']    ?? 1);
            $stmt  = mysqli_prepare($conn, 'INSERT INTO subject (ClassID,Name,Semester,DisplayOrder) VALUES (?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'issi', $class, $name, $sem, $order);

        } elseif ($entity === 'room') {
            $title = trim($_POST['room_title'] ?? '');
            $ttd   = trim($_POST['room_ttd']   ?? '');
            $stmt  = mysqli_prepare($conn, 'INSERT INTO room (Title,TTDName) VALUES (?,?)');
            mysqli_stmt_bind_param($stmt, 'ss', $title, $ttd);

        } elseif ($entity === 'period') {
            $title = trim($_POST['period_title']   ?? '');
            $num   = (int)($_POST['period_number'] ?? 0);
            $st    = $_POST['start']  ?? null;
            $et    = $_POST['end']    ?? null;
            $fst   = $_POST['fstart'] ?? null;
            $fet   = $_POST['fend']   ?? null;
            $order = (int)($_POST['period_order']  ?? 1);
            $stmt  = mysqli_prepare($conn, 'INSERT INTO period (Title,Number,StartTime,EndTime,FridayStartTime,FridayEndTime,DisplayOrder) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'sissssi', $title, $num, $st, $et, $fst, $fet, $order);
        }
       elseif ($entity === 'section') {
            $name      = trim($_POST['name']         ?? '');
            $ttdname   = trim($_POST['ttdname']      ?? '');
            $sem       = trim($_POST['semester']     ?? '');
            $order     = (int)($_POST['display_order'] ?? 0);
            $subjid    = (int)($_POST['subject_id']  ?? 0);
            $tchid     = (int)($_POST['teacher_id']  ?? 0);
            $subj_val  = $subjid ?: 'NULL';
            $tch_val   = $tchid  ?: 'NULL';
            $name_s    = mysqli_real_escape_string($conn, $name);
            $ttdname_s = mysqli_real_escape_string($conn, $ttdname);
            $sem_s     = mysqli_real_escape_string($conn, $sem);
            mysqli_query($conn, "INSERT INTO section (Name,TTDName,Semester,DisplayOrder,IsActive,SubjectID,TeacherID) VALUES ('$name_s','$ttdname_s','$sem_s',$order,1,$subj_val,$tch_val)")
                ? add_flash('success', 'Section added successfully.')
                : add_flash('error', mysqli_error($conn));
            redirect_to('admin/entities.php?tab=sections');
        }
    }

    if ($stmt) {
        if (mysqli_stmt_execute($stmt)) add_flash('success', 'Done successfully.');
        else                            add_flash('error',   'Failed: ' . mysqli_error($conn));
        mysqli_stmt_close($stmt);
    } elseif ($entity !== 'section') {
        add_flash('error', 'Invalid entity/action.');
    }
    redirect_to('admin/entities.php');
}

// ── GET: toggle / delete section ────────────────────────────────────────
if (isset($_GET['sec_delete'])) {
    $id    = (int)$_GET['sec_delete'];
    $check = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM timetablecelldetail WHERE SectionID=$id"))[0];
    if ($check > 0) {
        add_flash('error', 'Cannot delete: This section is used in timetable cells.');
    } else {
        mysqli_query($conn, "DELETE FROM section WHERE SectionID=$id")
            ? add_flash('success', 'Section deleted.')
            : add_flash('error', mysqli_error($conn));
    }
    redirect_to('admin/entities.php?tab=sections');
}
if (isset($_GET['sec_toggle'])) {
    $id  = (int)$_GET['sec_toggle'];
    $cur = mysqli_fetch_row(mysqli_query($conn, "SELECT IsActive FROM section WHERE SectionID=$id"))[0];
    $new = $cur == 1 ? 0 : 1;
    mysqli_query($conn, "UPDATE section SET IsActive=$new WHERE SectionID=$id");
    add_flash('success', 'Section status updated.');
    redirect_to('admin/entities.php?tab=sections');
}

// ───────────────────────────── fetch data ───────────────────────────────
$programs = fetch_all('SELECT * FROM program ORDER BY ProgramID DESC');
$classes  = fetch_all('SELECT c.*,p.Name ProgramName FROM `class` c LEFT JOIN program p ON p.ProgramID=c.ProgramID ORDER BY c.ClassID DESC');
$subjects = fetch_all('SELECT s.*,c.Name ClassName FROM subject s LEFT JOIN `class` c ON c.ClassID=s.ClassID ORDER BY s.SubjectID DESC LIMIT 100');
$rooms    = fetch_all('SELECT * FROM room ORDER BY RoomID DESC');
$periods  = fetch_all('SELECT * FROM period ORDER BY DisplayOrder,PeriodID');
$sections = fetch_all('SELECT s.*, sub.Name SubjectName, t.Name TeacherName,
    (SELECT COUNT(*) FROM timetablecelldetail tcd WHERE tcd.SectionID=s.SectionID) UsageCount
    FROM section s
    LEFT JOIN subject sub ON sub.SubjectID=s.SubjectID
    LEFT JOIN teacher t ON t.TeacherID=s.TeacherID
    ORDER BY s.Semester, s.DisplayOrder, s.Name');
$subjects_for_sec = fetch_all('SELECT * FROM subject ORDER BY Name');
$teachers_for_sec = fetch_all('SELECT * FROM teacher ORDER BY Name');

$activeTab = $_GET['tab'] ?? 'programs';

dashboard_start('Academic Entity Management', 'Programs, classes, subjects, rooms, periods and sections');
?>

<style>
.sec-avatar {
    width:44px;height:44px;border-radius:12px;
    background:linear-gradient(135deg,#0f766e,#14b8a6);
    color:#fff;font-size:1.1rem;font-weight:800;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.info-chip { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:50px;font-size:0.75rem;font-weight:600; }
.chip-subj { background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe; }
.chip-tch  { background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe; }
.chip-sem  { background:#fff7ed;color:#ea580c;border:1px solid #fed7aa; }
.chip-ord  { background:#f0fdf4;color:#059669;border:1px solid #bbf7d0; }
.chip-use  { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
.chip-use-zero { background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0; }
.toggle-active   { background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:50px;font-size:0.78rem;font-weight:700;cursor:pointer;border:none;text-decoration:none; }
.toggle-inactive { background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:50px;font-size:0.78rem;font-weight:700;cursor:pointer;border:none;text-decoration:none; }
</style>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editModalBody"></div>
    </div>
  </div>
</div>

<!-- Section Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="secModalTitle"><i class="fa-solid fa-layer-group me-2"></i>Add Section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="sec_action" value="1">
        <input type="hidden" name="entity" value="section">
        <div class="modal-body p-4">
          <input type="hidden" name="sec_id" id="secId" value="0">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-500">Section Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="secName" class="form-control" placeholder="e.g. A, B, Morning" required maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">TTD Name</label>
              <input type="text" name="ttdname" id="secTTDName" class="form-control" placeholder="Display name in timetable" maxlength="100">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Semester</label>
              <input type="text" name="semester" id="secSemester" class="form-control" placeholder="e.g. 1st, 2nd..." maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Display Order</label>
              <input type="number" name="display_order" id="secOrder" class="form-control" value="0" min="0" max="999">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Assign Subject</label>
              <select name="subject_id" id="secSubjectId" class="form-select">
                <option value="">-- No Subject --</option>
                <?php foreach ($subjects_for_sec as $sub): ?>
                  <option value="<?= (int)$sub['SubjectID'] ?>"><?= h($sub['Name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-500">Assign Teacher</label>
              <select name="teacher_id" id="secTeacherId" class="form-select">
                <option value="">-- No Teacher --</option>
                <?php foreach ($teachers_for_sec as $tch): ?>
                  <option value="<?= (int)$tch['TeacherID'] ?>"><?= h($tch['Name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:0.82rem;color:#64748b;">Quick Fill</label>
              <div class="d-flex flex-wrap gap-1">
                <?php foreach(['A','B','C','D','E','F','Morning','Evening','Regular','Weekend'] as $q): ?>
                  <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;"
                    onclick="document.getElementById('secName').value='<?= $q ?>'">
                    <?= $q ?>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Save Section</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Main Layout -->
<div class="row g-4">

  <!-- ADD FORM -->
  <div class="col-lg-4">
    <div class="data-card">
      <h5 class="fw-bold mb-3">Add Entity</h5>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
          <label class="form-label fw-bold">Entity</label>
          <select class="form-select" name="entity" id="entitySelect"
            onchange="document.querySelectorAll('.entity-fields').forEach(e=>e.classList.add('d-none'));document.getElementById('fields-'+this.value).classList.remove('d-none')">
            <option value="program">Program</option>
            <option value="class">Class</option>
            <option value="subject">Subject</option>
            <option value="room">Room</option>
            <option value="period">Period</option>
            <option value="section">Section</option>
          </select>
        </div>

        <div id="fields-program" class="entity-fields">
          <label class="form-label fw-bold">Program Name</label>
<select class="form-select mb-2" name="program_name">
  <option value="">-- Program Select --</option>
  <optgroup label="Computer Science">
    <option value="BS Computer Science">BS Computer Science</option>
    <option value="BS Information Technology">BS Information Technology</option>
    <option value="BS Software Engineering">BS Software Engineering</option>
    <option value="MS Computer Science">MS Computer Science</option>
    <option value="MCS">MCS</option>
  </optgroup>
  <optgroup label="Business">
    <option value="BBA">BBA</option>
    <option value="MBA">MBA</option>
  </optgroup>
  <optgroup label="Science">
    <option value="BS Mathematics">BS Mathematics</option>
    <option value="BS Physics">BS Physics</option>
    <option value="BS Chemistry">BS Chemistry</option>
  </optgroup>
  <optgroup label="Arts">
    <option value="BS English">BS English</option>
    <option value="BS Urdu">BS Urdu</option>
    <option value="BS Islamic Studies">BS Islamic Studies</option>
  </optgroup>
</select>

<label class="form-label fw-bold">Academic Type</label>
<select class="form-select mb-2" name="academic_type">
  <option value="">-- Academic Type Select --</option>
  <option value="Semester">Semester</option>
  <option value="Annual">Annual</option>
</select>
        </div>
        <div id="fields-class" class="entity-fields d-none">
         <label class="form-label fw-bold">Class Name</label>
<input class="form-control mb-2" name="class_name" placeholder="Class Name">

<label class="form-label fw-bold">Session</label>
<input class="form-control mb-2" name="session" placeholder="Session">

<label class="form-label fw-bold">TTD Name</label>
  <select class="form-select mb-2" name="ttdname">
    <option value="">-- TTD Name Select --</option>
    <optgroup label="Shift / Time">
      <option value="Mrng">Mrng (Morning)</option>
      <option value="AM">AM (Morning)</option>
      <option value="Evng">Evng (Evening)</option>
      <option value="PM">PM (Evening)</option>
      <option value="NT">NT (Night)</option>
    </optgroup>
 
  </select>

<label class="form-label fw-bold">Current Semester</label>
<input class="form-control mb-2" name="class_semester" placeholder="Current Semester">
          <select class="form-select mb-2" name="program_id">
              <label class="form-label fw-bold">Program</label>
            <option value="0">Select Program</option>
               <optgroup label="Programs">
      <option value="BSCS">BSCS</option>
      <option value="BSIT">BSIT</option>
      <option value="BSSE">BSSE</option>
      <option value="MCS">MCS</option>
      <option value="BBA">BBA</option>
      <option value="MBA">MBA</option>
    </optgroup>
  </select>
            <?php foreach ($programs as $p): ?>
              <option value="<?= (int)$p['ProgramID'] ?>"><?= h($p['Name']) ?></option>
            <?php endforeach; ?>
          </select>
          <textarea class="form-control mb-2" name="important_dates" placeholder="Important dates" rows="4"></textarea>
          <input type="color" class="form-control form-control-color" name="row_color" value="#ffffff">
        </div>
        <div id="fields-subject" class="entity-fields d-none">
          <select class="form-select mb-2" name="subject_class_id">
            <option value="0">Select Class</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= (int)$c['ClassID'] ?>"><?= h($c['Name']) ?></option>
            <?php endforeach; ?>
          </select>
          <label class="form-label fw-bold">Subject Name</label>
<input class="form-control mb-2" name="subject_name" placeholder="Subject Name">

<label class="form-label fw-bold">Semester</label>
<input class="form-control mb-2" name="subject_semester" placeholder="Semester">

<label class="form-label fw-bold">Display Order</label>
<input type="number" class="form-control mb-2" name="subject_order" value="1">
        </div>
        <div id="fields-room" class="entity-fields d-none">
        <label class="form-label fw-bold">Room Title</label>
<input class="form-control mb-2" name="room_title" placeholder="Room Title">

<label class="form-label fw-bold">TTD Name</label>
  <select class="form-select mb-2" name="ttdname">
    <option value="">-- TTD Name Select --</option>
    <optgroup label="Shift / Time">
      <option value="Mrng">Mrng (Morning)</option>
      <option value="AM">AM (Morning)</option>
      <option value="Evng">Evng (Evening)</option>
      <option value="PM">PM (Evening)</option>
      <option value="NT">NT (Night)</option>
    </optgroup>
  
      
  </select>
        </div>
        <div id="fields-period" class="entity-fields d-none">
         <label class="form-label fw-bold">Period Title</label>
<input class="form-control mb-2" name="period_title" placeholder="Period Title">

<label class="form-label fw-bold">Period Number</label>
<input type="number" class="form-control mb-2" name="period_number" placeholder="Number">
          <label class="small fw-bold">Normal Time</label>
          <input type="time" class="form-control mb-2" name="start">
          <input type="time" class="form-control mb-2" name="end">
          <label class="small fw-bold">Friday Time</label>
          <input type="time" class="form-control mb-2" name="fstart">
          <input type="time" class="form-control mb-2" name="fend">
          <label class="form-label fw-bold">Display Order</label>
<input type="number" class="form-control mb-2" name="period_order" value="1">
        </div>
        <div id="fields-section" class="entity-fields d-none">

  <label class="form-label fw-bold">Section Name</label>
  <select class="form-select mb-2" name="name">
    <option value="">-- Section Name Select --</option>
    <optgroup label="Alphabets">
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="C">C</option>
      <option value="D">D</option>
      <option value="E">E</option>
      <option value="F">F</option>
    </optgroup>
    <optgroup label="Shift">
      <option value="Morning">Morning</option>
      <option value="Evening">Evening</option>
      <option value="Night">Night</option>
    </optgroup>
    <optgroup label="Type">
      <option value="Regular">Regular</option>
      <option value="Weekend">Weekend</option>
    </optgroup>
  </select>

  <label class="form-label fw-bold">TTD Name</label>
  <select class="form-select mb-2" name="ttdname">
    <option value="">-- TTD Name Select --</option>
    <optgroup label="Shift / Time">
      <option value="Mrng">Mrng (Morning)</option>
      <option value="AM">AM (Morning)</option>
      <option value="Evng">Evng (Evening)</option>
      <option value="PM">PM (Evening)</option>
      <option value="NT">NT (Night)</option>
    </optgroup>
    <optgroup label="Section">
      <option value="Sec-A">Sec-A</option>
      <option value="Sec-B">Sec-B</option>
      <option value="Sec-C">Sec-C</option>
      <option value="Sec-D">Sec-D</option>
    </optgroup>
   
  <label class="form-label fw-bold">Semester</label>
  <optgroup label="Semester">
      <option value="Sem-1">Sem-1</option>
      <option value="Sem-2">Sem-2</option>
      <option value="Sem-3">Sem-3</option>
      <option value="Sem-4">Sem-4</option>
      <option value="Sem-5">Sem-5</option>
      <option value="Sem-6">Sem-6</option>
      <option value="Sem-7">Sem-7</option>
      <option value="Sem-8">Sem-8</option>
    </optgroup>

  <label class="form-label fw-bold">Display Order</label>
  <input type="number" class="form-control mb-2" name="display_order" placeholder="Display Order" value="0">

  <label class="form-label fw-bold">Subject (Optional)</label>
  <select class="form-select mb-2" name="subject_id">
    <option value="">-- No Subject --</option>
    <?php foreach ($subjects_for_sec as $sub): ?>
      <option value="<?= (int)$sub['SubjectID'] ?>"><?= h($sub['Name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label class="form-label fw-bold">Teacher (Optional)</label>
  <select class="form-select mb-2" name="teacher_id">
    <option value="">-- No Teacher --</option>
    <?php foreach ($teachers_for_sec as $tch): ?>
      <option value="<?= (int)$tch['TeacherID'] ?>"><?= h($tch['Name']) ?></option>
    <?php endforeach; ?>
  </select>

</div>

        <button class="btn btn-primary w-100">Save Entity</button>
      </form>
    </div>
  </div>

  <!-- TABLES -->
  <div class="col-lg-8">
    <div class="data-card">
      <ul class="nav nav-pills mb-3" id="entityTabs">
        <li class="nav-item"><button class="nav-link <?= $activeTab==='programs'?'active':'' ?>"  data-bs-toggle="pill" data-bs-target="#programs">Programs</button></li>
        <li class="nav-item"><button class="nav-link <?= $activeTab==='classes'?'active':'' ?>"   data-bs-toggle="pill" data-bs-target="#classes">Classes</button></li>
        <li class="nav-item"><button class="nav-link <?= $activeTab==='subjects'?'active':'' ?>"  data-bs-toggle="pill" data-bs-target="#subjects">Subjects</button></li>
        <li class="nav-item"><button class="nav-link <?= $activeTab==='rooms'?'active':'' ?>"     data-bs-toggle="pill" data-bs-target="#rooms">Rooms</button></li>
        <li class="nav-item"><button class="nav-link <?= $activeTab==='periods'?'active':'' ?>"   data-bs-toggle="pill" data-bs-target="#periods">Periods</button></li>
        <li class="nav-item"><button class="nav-link <?= $activeTab==='sections'?'active':'' ?>"  data-bs-toggle="pill" data-bs-target="#sections">Sections</button></li>
      </ul>

      <div class="tab-content">

        <!-- Programs -->
        <div class="tab-pane fade <?= $activeTab==='programs'?'show active':'' ?>" id="programs">
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><?php if ($programs) foreach (array_keys($programs[0]) as $k) echo '<th>'.h($k).'</th>'; ?><th>Actions</th></tr></thead>
            <tbody><?php foreach ($programs as $r): ?>
              <tr><?php foreach ($r as $v) echo '<td>'.h($v).'</td>'; ?>
                <td>
                  <button class="btn btn-sm btn-warning py-0" onclick="openEdit('program',<?=(int)$r['ProgramID']?>,<?=htmlspecialchars(json_encode($r),ENT_QUOTES)?>)">Edit</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="entity" value="program"><input type="hidden" name="record_id" value="<?=(int)$r['ProgramID']?>">
                    <button class="btn btn-sm btn-danger py-0">Del</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?></tbody>
          </table></div>
        </div>

        <!-- Classes -->
        <div class="tab-pane fade <?= $activeTab==='classes'?'show active':'' ?>" id="classes">
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><?php if ($classes) foreach (array_keys($classes[0]) as $k) echo '<th>'.h($k).'</th>'; ?><th>Actions</th></tr></thead>
            <tbody><?php foreach ($classes as $r): ?>
              <tr style="background:<?=h($r['RowColor']??'#fff')?>"><?php foreach ($r as $v) echo '<td>'.h($v).'</td>'; ?>
                <td>
                  <button class="btn btn-sm btn-warning py-0" onclick="openEdit('class',<?=(int)$r['ClassID']?>,<?=htmlspecialchars(json_encode($r),ENT_QUOTES)?>)">Edit</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="entity" value="class"><input type="hidden" name="record_id" value="<?=(int)$r['ClassID']?>">
                    <button class="btn btn-sm btn-danger py-0">Del</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?></tbody>
          </table></div>
        </div>

        <!-- Subjects -->
        <div class="tab-pane fade <?= $activeTab==='subjects'?'show active':'' ?>" id="subjects">
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><?php if ($subjects) foreach (array_keys($subjects[0]) as $k) echo '<th>'.h($k).'</th>'; ?><th>Actions</th></tr></thead>
            <tbody><?php foreach ($subjects as $r): ?>
              <tr><?php foreach ($r as $v) echo '<td>'.h($v).'</td>'; ?>
                <td>
                  <button class="btn btn-sm btn-warning py-0" onclick="openEdit('subject',<?=(int)$r['SubjectID']?>,<?=htmlspecialchars(json_encode($r),ENT_QUOTES)?>)">Edit</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="entity" value="subject"><input type="hidden" name="record_id" value="<?=(int)$r['SubjectID']?>">
                    <button class="btn btn-sm btn-danger py-0">Del</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?></tbody>
          </table></div>
        </div>

        <!-- Rooms -->
        <div class="tab-pane fade <?= $activeTab==='rooms'?'show active':'' ?>" id="rooms">
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><?php if ($rooms) foreach (array_keys($rooms[0]) as $k) echo '<th>'.h($k).'</th>'; ?><th>Actions</th></tr></thead>
            <tbody><?php foreach ($rooms as $r): ?>
              <tr><?php foreach ($r as $v) echo '<td>'.h($v).'</td>'; ?>
                <td>
                  <button class="btn btn-sm btn-warning py-0" onclick="openEdit('room',<?=(int)$r['RoomID']?>,<?=htmlspecialchars(json_encode($r),ENT_QUOTES)?>)">Edit</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="entity" value="room"><input type="hidden" name="record_id" value="<?=(int)$r['RoomID']?>">
                    <button class="btn btn-sm btn-danger py-0">Del</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?></tbody>
          </table></div>
        </div>

        <!-- Periods -->
        <div class="tab-pane fade <?= $activeTab==='periods'?'show active':'' ?>" id="periods">
          <div class="table-responsive"><table class="table table-sm">
            <thead><tr><?php if ($periods) foreach (array_keys($periods[0]) as $k) echo '<th>'.h($k).'</th>'; ?><th>Actions</th></tr></thead>
            <tbody><?php foreach ($periods as $r): ?>
              <tr><?php foreach ($r as $v) echo '<td>'.h($v).'</td>'; ?>
                <td>
                  <button class="btn btn-sm btn-warning py-0" onclick="openEdit('period',<?=(int)$r['PeriodID']?>,<?=htmlspecialchars(json_encode($r),ENT_QUOTES)?>)">Edit</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="entity" value="period"><input type="hidden" name="record_id" value="<?=(int)$r['PeriodID']?>">
                    <button class="btn btn-sm btn-danger py-0">Del</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?></tbody>
          </table></div>
        </div>

        <!-- Sections -->
        <div class="tab-pane fade <?= $activeTab==='sections'?'show active':'' ?>" id="sections">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small"><?= count($sections) ?> section(s)</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sectionModal" onclick="resetSecForm()">
              <i class="fa-solid fa-plus me-1"></i>Add Section
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>#</th><th>Section</th><th>TTD</th><th>Semester</th><th>Subject</th><th>Teacher</th><th>Order</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>
              <?php if (empty($sections)): ?>
                <tr><td colspan="10" class="text-center text-muted py-4">No sections added yet.</td></tr>
              <?php else: $i=1; foreach ($sections as $s): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="sec-avatar"><?= strtoupper(substr($s['Name'],0,1)) ?></div>
                      <div>
                        <div class="fw-bold"><?= h($s['Name']) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">ID: <?= $s['SectionID'] ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= $s['TTDName'] ? h($s['TTDName']) : '<span class="text-muted">—</span>' ?></td>
                  <td><?= $s['Semester'] ? '<span class="info-chip chip-sem">'.h($s['Semester']).'</span>' : '<span class="text-muted">—</span>' ?></td>
                  <td><?= $s['SubjectName'] ? '<span class="info-chip chip-subj">'.h($s['SubjectName']).'</span>' : '<span class="text-muted">—</span>' ?></td>
                  <td><?= $s['TeacherName'] ? '<span class="info-chip chip-tch">'.h($s['TeacherName']).'</span>' : '<span class="text-muted">—</span>' ?></td>
                  <td><span class="info-chip chip-ord"><?= (int)$s['DisplayOrder'] ?></span></td>
                  <td><?= $s['UsageCount'] > 0 ? '<span class="info-chip chip-use">'.(int)$s['UsageCount'].' cell(s)</span>' : '<span class="info-chip chip-use-zero">Not used</span>' ?></td>
                  <td>
                    <a href="?sec_toggle=<?= $s['SectionID'] ?>&tab=sections"
                       class="<?= $s['IsActive'] ? 'toggle-active' : 'toggle-inactive' ?>"
                       onclick="return confirm('Toggle status?')">
                      <?= $s['IsActive'] ? 'Active' : 'Inactive' ?>
                    </a>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-warning py-0"
                      data-bs-toggle="modal" data-bs-target="#sectionModal"
                      onclick="editSecForm(<?= $s['SectionID'] ?>,'<?= addslashes($s['Name']) ?>','<?= addslashes($s['TTDName']??'') ?>','<?= addslashes($s['Semester']??'') ?>',<?= (int)$s['DisplayOrder'] ?>,<?= (int)($s['SubjectID']??0) ?>,<?= (int)($s['TeacherID']??0) ?>)">
                      Edit
                    </button>
                    <a href="?sec_delete=<?= $s['SectionID'] ?>&tab=sections"
                       class="btn btn-sm btn-danger py-0"
                       onclick="return confirm('Delete section \'<?= addslashes($s['Name']) ?>\'?')">Del</a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /tab-content -->
    </div>
  </div>
</div>

<script>
const programOptions = `<option value="0">Select Program</option><?php foreach ($programs as $p) echo '<option value="'.(int)$p['ProgramID'].'">'.addslashes(h($p['Name'])).'</option>'; ?>`;
const classOptions   = `<option value="0">Select Class</option><?php foreach ($classes as $c) echo '<option value="'.(int)$c['ClassID'].'">'.addslashes(h($c['Name'])).'</option>'; ?>`;

function openEdit(entity, id, data) {
    let body = '';
    const f = (name, val, ph='', type='text') =>
        `<input class="form-control mb-2" name="${name}" type="${type}" placeholder="${ph}" value="${String(val??'').replace(/"/g,'&quot;')}">`;

    if (entity === 'program') {
        body = f('program_name', data.Name, 'Program Name') + f('academic_type', data.AcademicSystemType, 'Semester/Annual');
    } else if (entity === 'class') {
        let sel = programOptions.replace(`value="${data.ProgramID}"`, `value="${data.ProgramID}" selected`);
        body = f('class_name', data.Name, 'Class Name') + f('session', data.Session, 'Session') +
               f('class_ttd', data.TTDName, 'TTD Name') + f('class_semester', data.CurrentSemester, 'Current Semester') +
               `<select class="form-select mb-2" name="program_id">${sel}</select>` +
               `<textarea class="form-control mb-2" name="important_dates" rows="3">${String(data.ImportantDates??'').replace(/</g,'&lt;')}</textarea>` +
               `<input type="color" class="form-control form-control-color" name="row_color" value="${data.RowColor||'#ffffff'}">`;
    } else if (entity === 'subject') {
        let sel = classOptions.replace(`value="${data.ClassID}"`, `value="${data.ClassID}" selected`);
        body = `<select class="form-select mb-2" name="subject_class_id">${sel}</select>` +
               f('subject_name', data.Name, 'Subject Name') + f('subject_semester', data.Semester, 'Semester') +
               f('subject_order', data.DisplayOrder, 'Order', 'number');
    } else if (entity === 'room') {
        body = f('room_title', data.Title, 'Room Title') + f('room_ttd', data.TTDName, 'TTD Name');
    } else if (entity === 'period') {
        body = f('period_title', data.Title, 'Period Title') + f('period_number', data.Number, 'Number', 'number') +
               `<label class="small fw-bold">Normal Time</label>` +
               f('start', data.StartTime, '', 'time') + f('end', data.EndTime, '', 'time') +
               `<label class="small fw-bold">Friday Time</label>` +
               f('fstart', data.FridayStartTime, '', 'time') + f('fend', data.FridayEndTime, '', 'time') +
               f('period_order', data.DisplayOrder, 'Order', 'number');
    }

    document.getElementById('editModalBody').innerHTML = `
        <form method="POST">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="entity" value="${entity}">
          <input type="hidden" name="record_id" value="${id}">
          ${body}
          <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Update</button>
          </div>
        </form>`;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function resetSecForm() {
    document.getElementById('secModalTitle').innerHTML = '<i class="fa-solid fa-layer-group me-2"></i>Add Section';
    document.getElementById('secId').value = '0';
    document.getElementById('secName').value = '';
    document.getElementById('secTTDName').value = '';
    document.getElementById('secSemester').value = '';
    document.getElementById('secOrder').value = '0';
    document.getElementById('secSubjectId').value = '';
    document.getElementById('secTeacherId').value = '';
}

function editSecForm(id, name, ttdname, semester, order, subjectId, teacherId) {
    document.getElementById('secModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Edit Section';
    document.getElementById('secId').value = id;
    document.getElementById('secName').value = name;
    document.getElementById('secTTDName').value = ttdname;
    document.getElementById('secSemester').value = semester;
    document.getElementById('secOrder').value = order;
    document.getElementById('secSubjectId').value = subjectId || '';
    document.getElementById('secTeacherId').value = teacherId || '';
}
</script>

<?php dashboard_end(); ?>