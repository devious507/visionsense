<?php

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}
require_once("project.php");
require_once("security.php");

$sql="INSERT INTO email_alerts (mac) VALUES ('{$mac}')";
$db = connectDB();
$res=$db->query($sql);
checkDBError($res);
header("Location: emailAlerts.php?mac={$mac}");
?>
