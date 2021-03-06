<?php

require_once("project.php");
require_once("displayMatrix.php"); // for Configured Sensors List
require_once("security.php");
require_once("adminSecurity.php");

$linkTable="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
$linkTable.="<tr><td bgcolor=\"#cacaca\">Links</a></td></tr>";
$linkTable.="<tr><td><a href=\"sensorList.php\">Sensor List (Admin)</a></td></tr>\n";
$linkTable.="<tr><td><a href=\"userManager.php\">User Manager</a></td></tr>\n";
$linkTable.="<tr><td><a href=\"http://my.rtmscloud.com/\">Client Site</a></td></tr>\n";
$linkTable.="<tr><td><a href=\"logout.php\">Logout</a></td></tr>\n";
$linkTable.="</table>\n";

$db=connectDB();
$sql="SELECT * FROM orphans ORDER BY tstamp DESC";
$res=$db->query($sql);
$rows=$res->numRows();
if($rows == 0) {
	$orphanTable='';
} else {
	$orphanTable="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
	$orphanTable.="<tr><td colspan=\"3\" bgcolor=\"#cacaca\">Orphaned Sensor Packages</a></td></tr>";
	$orphanTable.="<tr><td bgcolor=\"#cacaca\">Sensor-ID</td>";
	$orphanTable.="<td bgcolor=\"#cacaca\" colspan=\"2\">Timestamp</td></tr>";
	while(($row=$res->fetchRow())==true) {
		$watcher="<a href=\"watchOrphan.php?mac={$row[0]}\"><img width=\"16\" height=\"16\" src=\"images/icons/refresh.png\"></a>";
		$img="<img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\">";
		$deleteOrphan= "<a href=\"deleteOrphan.php?mac={$row[0]}\">{$img}</a>";
		$link="<a href=\"claimOrphan.php?mac={$row[0]}\">{$row[0]}</a> {$deleteOrphan}";
		$link2="<a href=\"http://my.rtmscloud.com/resetHistory.php?mac={$row[0]}\" target=\"top\">Reset Log</a>";
		$orphanTable.="<tr><td>{$link}</td><td>{$row[1]} {$watcher}</td><td>{$link2}</td></tr>";
	}
	$orphanTable.="</table>\n";
	$orphanTable.="<hr width=\"800\" align=\"left\">\n";
}

/*
$bg="bgcolor=\"#cacaca\"";
$sensorTable="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
$sensorTable.="<tr><td colspan=\"11\" {$bg}>Configured Sensors</td></tr>";
$sensorTable.="<tr><td {$bg}>MAC</td><td {$bg}>Owner</td><td {$bg}>Description</td><td {$bg}>Water</td><td {$bg}>Electric</td><td {$bg}>Supply</td><td {$bg}>Return</td><td {$bg}>Supply2</td><td {$bg}>Return2</td><td {$bg}>H20</td><td {$bg}>Room</td></tr>";
$sql="SELECT mac FROM sensor_setup ORDER BY description";
$res=$db->query($sql);
checkDBError($res);
while(($row=$res->fetchRow())==true) {
	$sensorTable.=getRow($row[0]);
}

/*
$sql="SELECT s.mac,s.description,u.email,s.owner FROM sensor_setup AS s LEFT OUTER JOIN users AS u ON s.owner=u.userid ORDER BY s.description,s.mac";
$res=$db->query($sql);
checkDBError($res,$sql);
while(($row=$res->fetchRow())==true) {
	$img="<img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\">";
	$deleteSensor = "<a href=\"deleteSensor.php?mac={$row[0]}\">{$img}</a>";
	$editSensor="<a href=\"http://my.rtmscloud.com/editBuilding.php?mac={$row[0]}\">{$row[0]}</a>";
	$changeOwner = $row[2];
	$sensorTable.="<tr><td>{$row[3]}</td><td>{$editSensor}&nbsp;{$deleteSensor}</td><td>{$row[1]}</td><td>{$changeOwner}</td></tr>";
}

$sensorTable.="</table>\n";
$sensorTable.="<hr width=\"800\" align=\"left\">\n";
 */
?>
<!DOCTYPE html>
<html>
<head>
<title>VisionSense Admin</title>
</head>
<body>
<?php echo $orphanTable; ?>
<?php echo $linkTable; ?>
</body>
</html>
