<?php

require_once("project.php");
require_once("security.php");

$exists=array("id","group_name");
foreach($exists as $e) {
	if(!isset($_POST[$e])) {
		header("Location: manageGroup.php");
	}
}
$id=$_POST['id'];
$name=$_POST['group_name'];

$db=connectDB();
$sql="SELECT owner_id FROM sensor_groups WHERE id={$id}";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$ownerID = $row[0];
if($ownerID == $_COOKIE['ownerID']) {
	$sql="UPDATE sensor_groups SET group_name='{$name}' WHERE id={$id}";
	$res=$db->query($sql);
	checkDBError($res);
	header("Location: manageGroup.php");
	exit();
} else {
	header("Location: index.php");
}
?>
