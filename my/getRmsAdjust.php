<?php

require_once("project.php");

if(!isset($_GET['mac'])) {
	exit();
} else {
	$mac=substr($_GET['mac'],0,14);
}

$db = connectDB();
$sql="SELECT rmsadjust FROM sensor_setup WHERE mac='{$mac}'";
$res = $db->query($sql);
checkDBError($res,$sql);
if($res->numRows() == 1) {
	$row=$res->fetchRow();
	print $row[0];
	exit();
} else {
	$sql="SELECT count(*) as c FROM orphans WHERE mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res);
	$numRows = $res->numRows();
	if($numRows == 0) {
		$sql="INSERT INTO orphans (mac) VALUES ('{$mac}')";
		$res=$db->query($sql);
		checkDBError($res,$sql);
	}
	print "0";
	exit();
}
?>
