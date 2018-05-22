<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}
if(!isset($_GET['lines'])) {
	$lines=10;
} else {
	$lines=$_GET['lines'];
}

$db=connectDB();
$sql="SELECT date_trunc('second',tstamp) as tstamp FROM reset_log WHERE mac='{$mac}' ORDER BY tstamp DESC LIMIT {$lines}";
$res=$db->query($sql);
checkDBError($res,$sql);
$tData='';
while(($row=$res->fetchRow())==true) {
	$tData.="<tr><td>{$row[0]}</td></tr>\n";
}
$link="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
?>
<!DOCTYPE html>
<head>
<title>Reset History</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td bgcolor="#cacaca">Reset Log <?php echo $link; ?></td></tr>
<?php echo $tData; ?>
</table>
</body>
</html>
