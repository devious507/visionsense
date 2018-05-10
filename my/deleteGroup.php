<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['id'])) {
	header("Location: manageGroup.php");
} else {
	$id=$_GET['id'];
}

$db=connectDB();
$sql="SELECT id FROM sensor_groups WHERE group_name=' Default' AND owner_id={$_COOKIE['ownerID']}";
$res=$db->query($sql);
checkDBError($res,$sql);
if($res->numRows() == 0) {
	header("Location: manageGroup.php");
}
$row=$res->fetchRow();
$myDefault = $row[0];
// Assign any Sensors from this group to the default group
$sql="UPDATE sensor_setup SET sensor_group={$myDefault} WHERE sensor_group={$id}";
$res=$db->query($sql);
checkDBError($res,$sql);
// Delete the group
$sql="DELETE from sensor_groups WHERE id={$id} AND immutable=false AND owner_id={$_COOKIE['ownerID']}";
$res=$db->query($sql);
checkDBError($res,$sql);
header("Location: manageGroup.php");
?>
