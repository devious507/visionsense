<?php

require_once("project.php");
require_once("security.php");


if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}

$db=connectDB();
$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res);
$row=$res->fetchRow();
$myAddress=$row[0];

$sql="SELECT owner FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if(($row[0] != $_COOKIE['ownerID']) && ($_COOKIE['superadmin'] != 't')) {
	header("Location: index.php");
}

$sql="SELECT id,sortorder,h_lbl,v_lbl,width,height,timeframe FROM graph_master WHERE mac='{$mac}' ORDER BY sortorder ASC";
$res=$db->query($sql);
checkDBError($res,$sql);
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$id=$row['id'];
	$sort=$row['sortorder'];
	$lbl="({$sort}) {$row['h_lbl']}";

	if($row['h_lbl'] == '') {
		$lbl = "({$sort}) unlabled graph";
	}
	$del="<a href=\"deleteGraph.php?mac={$mac}&id={$id}\"><img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\"></a>";
	$links[]="<li><a href=\"editGraph.php?mac={$mac}&id={$id}\">{$lbl}</a> {$del}</li>\n";
}
$list=implode("\n",$links);
$backLink="<a href=\"editBuilding.php?mac={$mac}\">Back</a>";
$newLink="<a href=\"createGraph.php?mac={$mac}\">Add New Graph</a>";
$address="<tr><td colspan=\"2\" bgcolor=\"#cacaca\"><b>{$myAddress}</b></td></tr>\n";
$sensorID="<tr><td colspan=\"2\" bgcolor=\"#cacaca\"><b>Sensor-ID: {$mac}</b></td></tr>\n";
?>
<!DOCTYPE html>
<html>
<head><title>My Graphs</title></head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $address; ?>
<?php echo $sensorID; ?>
<tr><td><?php echo $backLink; ?></td><td><?php echo $newLink; ?></td></tr>
<tr><td colspan="2"><ul> <?php echo $list; ?> </ul></td></tr>
</table>
</body>
</html>
