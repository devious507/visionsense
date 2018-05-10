<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
}
$sql="INSERT INTO graph_master (mac) values ('{$_GET['mac']}')";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
header("Location: graphManagement.php?mac={$_GET['mac']}");
?>
