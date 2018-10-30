<?php

require_once("project.php");
require_once("security.php");

if(isset($_GET['startDateOnly']) && isset($_GET['startTimeOnly'])) {
	$startDateOnly = $_GET['startDateOnly'];
	$startTimeOnly = $_GET['startTimeOnly'];
	$start = $startDateOnly."T".$startTimeOnly;
} else {
	$startDateOnly = date("Y-m-d");
	$startTimeOnly = "00:00";
	$start = date("Y-m-d")."T00:00";
}
if(isset($_GET['endDateOnly']) && isset($_GET['endTimeOnly'])) {
	$endDateOnly = $_GET['endDateOnly'];
	$endTimeOnly = $_GET['endTimeOnly'];
	$end = $endDateOnly."T".$endTimeOnly;
} else {
	$endDateOnly = date("Y-m-d");
	$endTimeOnly = "23:59";
	$end = date("Y-m-d")."T23:59";
}

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}
if(!isset($_GET['lines'])) {
	$lines=9999;
} else {
	$lines=$_GET['lines'];
}

$startVal=$endVal="";
if(isset($start)) {
	$startDateVal = "value=\"{$startDateOnly}\"";
	$startTimeVal = "value=\"{$startTimeOnly}\"";
}
if(isset($end)) {
	$endDateVal = "value=\"{$endDateOnly}\"";
	$endTimeVal = "value=\"{$endTimeOnly}\"";
}

$linkArray="<form method=\"get\" action=\"resetHistory.php\"><input type=\"hidden\" name=\"mac\" value=\"{$mac}\">
	<input {$startDateVal} type=\"date\" name=\"startDateOnly\"> <input {$startTimeVal} type=\"time\" name=\"startTimeOnly\"> -
	<input {$endDateVal} type=\"date\" name=\"endDateOnly\"> <input {$endTimeVal} type=\"time\" name=\"endTimeOnly\"><input type=\"submit\" value=\"Go\"></form>";

$db=connectDB();
$sql="SELECT date_trunc('second',tstamp) as tstamp FROM reset_log WHERE mac='{$mac}' AND tstamp >= '{$start}' AND tstamp <= '{$end}' ORDER BY tstamp DESC LIMIT {$lines}";
$res=$db->query($sql);
checkDBError($res,$sql);
$tData='';
while(($row=$res->fetchRow())==true) {
	$tData.="<tr><td>{$row[0]}</td></tr>\n";
}
$link="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
print pageHeader("Sensor Package Resets",true,300,1,400);
?>
<tr><td bgcolor="#cacaca">Reset Log <?php echo $link; echo "   "; echo date("h:i:s"); ?><hr><?php echo $linkArray; ?></td></tr>
<?php echo $tData; ?>
</table>
</body>
</html>
