<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');
if(isset($_GET['download'])){
  $filename='ctt_backup_'.date('Ymd_His').'.sql';
  header('Content-Type: application/sql');header('Content-Disposition: attachment; filename='.$filename);
  echo "-- College Time Tracker Backup\n-- Generated: ".date('Y-m-d H:i:s')."\n\n";
  $tables=[];$res=mysqli_query($conn,'SHOW TABLES');while($row=mysqli_fetch_row($res)){$tables[]=$row[0];}
  foreach($tables as $table){
    $create=mysqli_fetch_row(mysqli_query($conn,'SHOW CREATE TABLE `'.$table.'`'));
    echo "DROP TABLE IF EXISTS `$table`;\n".$create[1].";\n\n";
    $data=mysqli_query($conn,'SELECT * FROM `'.$table.'`');
    while($r=mysqli_fetch_assoc($data)){
      $cols=[];$vals=[];
      foreach(array_keys($r) as $c){$cols[]='`'.$c.'`';}
      foreach(array_values($r) as $v){$vals[]=$v===null?'NULL':"'".mysqli_real_escape_string($conn,$v)."'";}
      echo 'INSERT INTO `'.$table.'` ('.implode(',',$cols).') VALUES ('.implode(',',$vals).');' . "\n";
    }
    echo "\n";
  }
  exit;
}
dashboard_start('Database Backup','Download SQL backup for phpMyAdmin restore');
?>
<div class="row justify-content-center"><div class="col-lg-8"><div class="panel-card text-center p-5"><div class="display-1 text-primary"><i class="fa-solid fa-database"></i></div><h3 class="fw-bold mt-3">Database Backup / Restore</h3><p class="text-muted" style="line-height:1.8;">Click the button below to download a full SQL backup of the current CTT database. To restore, open phpMyAdmin, select database, click Import, and upload the downloaded SQL file.</p><a class="btn btn-main btn-lg" href="?download=1"><i class="fa-solid fa-download me-2"></i>Download SQL Backup</a><div class="alert alert-warning mt-4 text-start"><b>Note:</b> Restore is done safely from phpMyAdmin. This prevents accidental overwrite from the browser.</div></div></div></div>
<?php dashboard_end(); ?>
