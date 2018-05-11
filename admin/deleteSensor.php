<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
	exit();
} else {
	$mac=$_GET['mac'];
}
if(!isset($_GET['confirm']) || $_GET['confirm'] != 'true') {
	print "<!DOCTYPE html>";
	print "<html><head><title>Really Delete Sensor?</title></head>";
	print "<body><p><img width=\"500\" src=\"images/warning-banner.png\"></p><div style=\"width: 500px;\"<p><b>WARNING</b> you are about to delete sensor <b><u>{$mac}</u></b> from the system.  This action is 100% irreversable, and cannot be undone even by ";
	print "the site owner or administrator.  Only click below if you are <b>certain</b> that you wish to complete thie irreversable and unrevokable action.</p>";
	print "<p>This action will cause the permanent loss of <u>all</u> history and graphs data associated with Sensor-ID: <b>{$mac}</b></p>";
	print "<p><a href=\"deleteSensor.php?mac={$mac}&confirm=true\">I UNDERSTAND THAT BY CLICKING THIS LINK I WILL BE PERMANENTLY DELETING SENSOR {$mac} FROM THE SYSTEM!</a></p>";
	print "<p><a href=\"index.php\">I've changed my mind</a> get me out of here!</a></p>";
	print "</div></body></html>";
	exit();
}

$db=connectDB();
$sql="SELECT id FROM graph_master WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
while(($row=$res->fetchRow())==true) {
	$sql2="DELETE FROM graph_items WHERE graphid='{$row[0]}'";
	$res2=$db->query($sql2);
	checkDBError($res,$sql2);
}

$sql="DELETE FROM graph_master WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);

$sql="DELETE FROM sensor_log WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);

$sql="DELETE FROM sensor_current WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);

$sql="DELETE FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);

header("Location: index.php");
?>
