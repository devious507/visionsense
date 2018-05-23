<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Logging Menu</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td bgcolor="#cacaca">Log Menu</td></tr>
<tr><td><a href="sensorHistory.php?mac=<?php echo $mac;?>&lines=25">Sensor History</a></td></tr>
<tr><td><a href="alarmHistory.php?mac=<?php echo $mac;?>&lines=25">Alarm History</a></td></tr>
<tr><td><a href="resetHistory.php?mac=<?php echo $mac;?>&lines=25">Sensor Package Resets</a></td></tr>
<tr><td><a href="sensorDetail.php?mac=<?php echo $mac;?>">Back</a></td></tr>
</table>
</body>
</html>