<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}
print pageHeader("Logging Menu",true,0,1,400);
?>
<tr><td bgcolor="#cacaca">Log Menu</td></tr>
<tr><td><a href="sensorHistory.php?mac=<?php echo $mac;?>">Sensor History</a></td></tr>
<tr><td><a href="alarmHistory.php?mac=<?php echo $mac;?>">Alarm History</a></td></tr>
<tr><td><a href="resetHistory.php?mac=<?php echo $mac;?>">Sensor Package Resets</a></td></tr>
<tr><td><a href="sensorDetail.php?mac=<?php echo $mac;?>">Back</a></td></tr>
</table>
</body>
</html>
