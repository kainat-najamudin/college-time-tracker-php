<?php
require_once __DIR__ . '/../includes/layout.php';
require_any_role(['admin','incharge']);
$roleNow=current_role();

$ttid = (int)($_GET['ttid'] ?? scalar_query('SELECT TTID FROM timetable ORDER BY TTID DESC LIMIT 1'));
$classFilter = (int)($_GET['class_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $ttidPost=(int)$_POST['ttid'];$classId=(int)$_POST['class_id'];$periodId=(int)$_POST['period_id'];$subjectId=(int)$_POST['subject_id'];$teacherId=(int)$_POST['teacher_id'];$sectionId=(int)($_POST['section_id'] ?? 0);$roomId=(int)$_POST['room_id'];$days=trim($_POST['days']);$color=trim($_POST['cell_color'] ?: '#ffffff');$notes=trim($_POST['notes']);$order=(int)($_POST['display_order'] ?? 1);
    if(!$ttidPost || !$classId || !$periodId || !$subjectId || !$teacherId || !$roomId || !$days){ add_flash('error','Please fill all required timetable fields.'); }
    else {
        $conflicts=check_timetable_conflict($ttidPost,$periodId,$teacherId,$roomId,$days);
        if($conflicts){ $first=$conflicts[0]; add_flash('error','Conflict found: '.$first['TeacherName'].' or '.$first['RoomTitle'].' already assigned in '.$first['ClassName'].' for '.$first['Days'].'.'); }
        else { $cellId=get_or_create_cell($ttidPost,$classId,$periodId); $stmt=mysqli_prepare($conn,'INSERT INTO timetablecelldetail (TTCID,SubjectID,TeacherID,SectionID,RoomID,Days,DisplayOrder,CellColor,Notes) VALUES (?,?,?,?,?,?,?,?,?)'); mysqli_stmt_bind_param($stmt,'iiiiisiss',$cellId,$subjectId,$teacherId,$sectionId,$roomId,$days,$order,$color,$notes); if(mysqli_stmt_execute($stmt)) add_flash('success','Timetable entry added successfully.'); else add_flash('error','Failed: '.mysqli_error($conn)); mysqli_stmt_close($stmt); }
    }
    redirect_to(($roleNow==='admin'?'admin':'incharge').'/timetable.php?ttid='.$ttidPost);
}

if (isset($_GET['delete_detail'])) {
    $id=(int)$_GET['delete_detail'];
    mysqli_query($conn,"DELETE FROM timetablecelldetail WHERE TTCDID=$id");
    add_flash('success','Timetable entry deleted.');
    redirect_to(($roleNow==='admin'?'admin':'incharge').'/timetable.php?ttid='.$ttid);
}

if (isset($_GET['export']) && $_GET['export']==='csv') {
    $where = $classFilter ? ' AND c.ClassID=' . $classFilter : '';
    $rows = fetch_all("SELECT c.Name AS ClassName,p.Title AS Period,p.StartTime,p.EndTime,p.FridayStartTime,p.FridayEndTime,d.Days,s.Name AS Subject,t.Name AS Teacher,r.Title AS Room,d.Notes FROM timetablecelldetail d JOIN timetablecell cell ON cell.TTCID=d.TTCID JOIN `class` c ON c.ClassID=cell.ClassID JOIN period p ON p.PeriodID=cell.PeriodID LEFT JOIN subject s ON s.SubjectID=d.SubjectID LEFT JOIN teacher t ON t.TeacherID=d.TeacherID LEFT JOIN room r ON r.RoomID=d.RoomID WHERE cell.TTID=$ttid $where ORDER BY c.ClassID,p.DisplayOrder,d.DisplayOrder");
    $csv=[];foreach($rows as $r){$csv[]=[$r['ClassName'],$r['Period'],format_time_short($r['StartTime']).'-'.format_time_short($r['EndTime']),format_time_short($r['FridayStartTime']).'-'.format_time_short($r['FridayEndTime']),$r['Days'],$r['Subject'],$r['Teacher'],$r['Room'],$r['Notes']];}
    export_csv('ctt-timetable.csv',['Class','Period','Time','Friday Time','Days','Subject','Teacher','Room','Notes'],$csv);
}

$timetables=fetch_all('SELECT * FROM timetable ORDER BY TTID DESC');
$classes=fetch_all('SELECT * FROM `class` WHERE IsActive=1 '.($classFilter?' AND ClassID='.$classFilter:'').' ORDER BY ClassID');
$allClasses=fetch_all('SELECT * FROM `class` WHERE IsActive=1 ORDER BY ClassID');
$periods=fetch_all('SELECT * FROM period ORDER BY DisplayOrder,PeriodID');
$subjects=fetch_all('SELECT * FROM subject ORDER BY Name');
$teachers=fetch_all('SELECT * FROM teacher ORDER BY Name');
$rooms=fetch_all('SELECT * FROM room ORDER BY Title');
$sections=fetch_all('SELECT * FROM section ORDER BY Name');
$detailsRows=fetch_all("SELECT d.*,cell.ClassID,cell.PeriodID,s.Name SubjectName,t.Name TeacherName,r.Title RoomTitle FROM timetablecelldetail d JOIN timetablecell cell ON cell.TTCID=d.TTCID LEFT JOIN subject s ON s.SubjectID=d.SubjectID LEFT JOIN teacher t ON t.TeacherID=d.TeacherID LEFT JOIN room r ON r.RoomID=d.RoomID WHERE cell.TTID=$ttid ORDER BY d.DisplayOrder,d.TTCDID");
$grid=[];foreach($detailsRows as $d){$grid[$d['ClassID'].'_'.$d['PeriodID']][]=$d;}
$ttName=scalar_query("SELECT COUNT(*) FROM timetable WHERE TTID=$ttid") ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT Name FROM timetable WHERE TTID=$ttid"))['Name'] : 'Timetable';

dashboard_start('Timetable Management','Image-style grid view with conflict check, print and export');
?>
<div class="data-card mb-4 no-print">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3"><div><h5 class="fw-bold mb-1">Timetable Controls</h5><p class="text-muted mb-0">Select timetable/class, print, export CSV, or download as image.</p></div><div class="toolbar"><button class="btn btn-outline-dark" onclick="printTimetable()"><i class="fa-solid fa-print me-1"></i>Print</button><button class="btn btn-outline-success" onclick="exportTimetableCsv()"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</button><button class="btn btn-outline-primary" onclick="downloadTimetableImage()"><i class="fa-solid fa-image me-1"></i>Download Image</button><button class="btn btn-main" data-bs-toggle="collapse" data-bs-target="#addEntry"><i class="fa-solid fa-plus me-1"></i>Add Entry</button></div></div>
  <form class="row g-2 mt-3" id="filterForm" method="GET"><div class="col-md-4"><select class="form-select" name="ttid" onchange="filterTimetable()"><?php foreach($timetables as $tt): ?><option value="<?= (int)$tt['TTID'] ?>" <?= $ttid==(int)$tt['TTID']?'selected':'' ?>><?= h($tt['Name']) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><select class="form-select" name="class_id" onchange="filterTimetable()"><option value="0">All Classes</option><?php foreach($allClasses as $c): ?><option value="<?= (int)$c['ClassID'] ?>" <?= $classFilter==(int)$c['ClassID']?'selected':'' ?>><?= h($c['Name']) ?></option><?php endforeach; ?></select></div></form>
  <div class="collapse mt-3" id="addEntry"><div class="panel-card"><form method="POST" class="row g-3"><input type="hidden" name="ttid" value="<?= $ttid ?>"><div class="col-md-3"><label class="form-label fw-bold">Class</label><select name="class_id" class="form-select" required><?php foreach($allClasses as $c): ?><option value="<?= (int)$c['ClassID'] ?>"><?= h($c['Name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label fw-bold">Period</label><select name="period_id" class="form-select" required><?php foreach($periods as $p): ?><option value="<?= (int)$p['PeriodID'] ?>"><?= h($p['Title']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label fw-bold">Subject</label><select name="subject_id" class="form-select" required><?php foreach($subjects as $s): ?><option value="<?= (int)$s['SubjectID'] ?>"><?= h($s['Name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label fw-bold">Teacher</label><select name="teacher_id" class="form-select" required><?php foreach($teachers as $t): ?><option value="<?= (int)$t['TeacherID'] ?>"><?= h($t['Name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label fw-bold">Room</label><select name="room_id" class="form-select" required><?php foreach($rooms as $r): ?><option value="<?= (int)$r['RoomID'] ?>"><?= h($r['Title']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label fw-bold">Section</label><select name="section_id" class="form-select"><option value="0">None</option><?php foreach($sections as $s): ?><option value="<?= (int)$s['SectionID'] ?>"><?= h($s['Name']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label fw-bold">Days</label><input name="days" class="form-control" placeholder="MO-TU-WE-TH-FR-SA" required></div><div class="col-md-2"><label class="form-label fw-bold">Color</label><input type="color" name="cell_color" value="#ffffff" class="form-control form-control-color"></div><div class="col-md-1"><label class="form-label fw-bold">Order</label><input type="number" name="display_order" value="1" class="form-control"></div><div class="col-md-4"><label class="form-label fw-bold">Notes</label><input name="notes" class="form-control" placeholder="Optional note"></div><div class="col-md-12"><button class="btn btn-main"><i class="fa-solid fa-shield-halved me-1"></i>Check Conflict & Save</button></div></form></div></div>
</div>
<h3 class="print-title"><?= h($ttName) ?></h3>
<div class="timetable-wrap" id="timetableGrid"><table class="ctt-table"><thead><tr><th class="class-head" rowspan="2">Class</th><th class="dates-head" rowspan="2">Important<br>Dates</th><th rowspan="2" style="width:36px"></th><?php foreach($periods as $p): ?><th class="period-head"><div class="period-title">Period<br><?= h($p['Number']) ?></div></th><?php endforeach; ?></tr><tr><?php foreach($periods as $p): ?><th class="period-head"><div class="period-time"><?= h(format_time_short($p['StartTime']).' - '.format_time_short($p['EndTime'])) ?></div><div class="period-fr"><?= h(format_time_short($p['FridayStartTime']).' - '.format_time_short($p['FridayEndTime'])) ?> (FR)</div></th><?php endforeach; ?></tr></thead><tbody><?php $rowspan=max(1,count($classes)); foreach($classes as $i=>$c): ?><tr><td class="class-cell" style="background:<?= h($c['RowColor']) ?>"><?= h($c['Name']) ?></td><td class="important-cell"><?= h($c['ImportantDates']) ?></td><?php if($i===0): ?><td class="control-cell" rowspan="<?= $rowspan ?>">Zero Period / Group Discussions / Dengue Control 08:00 - 08:30</td><?php endif; ?><?php foreach($periods as $p): $key=$c['ClassID'].'_'.$p['PeriodID']; ?><td class="tt-cell"><?php foreach($grid[$key] ?? [] as $d): ?><div class="tt-entry" style="background:<?= h($d['CellColor']) ?>"><div class="tt-entry-days"><?= h($d['Days']) ?></div><div class="tt-entry-subject"><?= h($d['SubjectName']) ?></div><div class="tt-entry-meta"><?= h($d['TeacherName']) ?><br><?= h($d['RoomTitle']) ?><?php if($d['Notes']): ?><br><?= h($d['Notes']) ?><?php endif; ?></div><?php if(in_array($roleNow,['admin','incharge'],true)): ?><a class="small text-danger no-print" onclick="return confirm('Delete entry?')" href="?ttid=<?= $ttid ?>&delete_detail=<?= (int)$d['TTCDID'] ?>">Delete</a><?php endif; ?></div><?php endforeach; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php dashboard_end(); ?>
