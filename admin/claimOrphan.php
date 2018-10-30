<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}

$db = connectDB();
$sql="INSERT INTO sensor_setup (mac,owner) VALUES ('{$mac}',1)";
$res=$db->query($sql);
checkDBError($res,$sql);
$sql="DELETE FROM orphans WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
header("Location: http://my.rtmscloud.com/editBuilding.php?mac={$mac}");
//header("Location: index.php");
?>
