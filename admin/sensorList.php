<?php

require_once("project.php");
require_once("security.php");


if(isset($_GET['mac'])) {
	header("Location: http://my.rtmscloud.com/sensorDetail.php?mac={$_GET['mac']}");
	exit();
} else {
	$sql="SELECT * FROM sensor_setup ORDER BY owner,sensor_group,description,mac";
	if(isset($_GET['mode']) && $_GET['mode']=='nocm') {
		$sql="SELECT * FROM sensor_setup WHERE cm IS NULL ORDER BY owner,sensor_group,description,mac";
	}
}

$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
$sensors=$res->fetchAll(MDB2_FETCHMODE_ASSOC);
$rows=array();
$sensorGroup=-1;
$sensorGroupName="jekylandhyde";
foreach($sensors as $s) {
	$owner=getOwner($s['owner']);
	if($sensorGroup != $s['sensor_group']) {
		$sql="SELECT group_name FROM sensor_groups WHERE id={$s['sensor_group']}";
		$lRes=$db->query($sql);
		checkDBError($lRes);
		$lRow=$lRes->fetchRow();
		$sensorGroupName=$lRow[0];
		$sensorGroup=$s['sensor_group'];
		$rows[]="<tr><td bgcolor=\"#cacaca\" colspan=\"16\"><hr><b>({$owner}) {$sensorGroupName}:</b> <a href=\"http://my.rtmscloud.com/graphGroups.php?group=Boiler Temp&building_group={$sensorGroupName}\">Boiler Graphs</a>, <a href=\"http://my.rtmscloud.com/graphGroups.php?group=Water&building_group={$sensorGroupName}\">Water Graphs</a>, <a href=\"http://my.rtmscloud.com/graphGroups.php?group=Electrical&building_group={$sensorGroupName}\">Electrical Graphs</a><hr></td></tr>";
		$rows[]="<tr> <td bgcolor=\"#ececec\" rowspan=\"2\">Description</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Water</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Electric</td> <td bgcolor=\"#ececec\" align=\"center\" colspan=\"2\">Boiler 1</td> <td bgcolor=\"#ececec\" align=\"center\" colspan=\"2\">Boiler 2</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Room</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Water</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Lockout</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Lockout2</td> <td bgcolor=\"#ececec\" align=\"center\" colspan=\"2\">Doors</td> <td align=\"center\" rowspan=\"2\" bgcolor=\"#ececec\">Tamper</td> </tr>";
		$rows[]="<tr> <td align=\"center\" bgcolor=\"#ececec\">Supply</td> <td align=\"center\" bgcolor=\"#ececec\">Return</td> <td align=\"center\" bgcolor=\"#ececec\">Supply</td> <td align=\"center\" bgcolor=\"#ececec\">Return</td> <td align=\"center\" bgcolor=\"#ececec\">Boiler Rm</td> <td align=\"center\" bgcolor=\"#ececec\">Distro Rm</td> </tr>";
	}
	$cells=getSensorCells($s);
	$rows[]="<tr>".implode("\n\t",$cells)."</tr>\n";
}

function getOwner($owner) {
	$db=connectDB();
	$sql="select username FROM users WHERE userid={$owner}";
	$res=$db->query($sql);
	checkDBError($res);
	$row=$res->fetchRow();
	return $row[0];
}

?>
<!DOCTYPE html>
<html><head><meta http-equiv="refresh" content="180">
<title>Smart Building List</title></head>
<body><table cellpadding="5" cellspacing="0" border="1">
<tr>
	<td bgcolor="#cacaca" colspan="16"><a href="index.php"><img src="images/visionSecurityLogo.png"></a></td>
</tr>
<?php echo implode("\n",$rows); ?>
<tr><td colspan="16"><a href="manageGroup.php">My Groups</a> || <a href="logout.php">Logout</a> || <a href="graphGroups.php">Graph Groups</a></td></tr>
</table>
</body>
</html>
