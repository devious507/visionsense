<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

$mac='abcd.abcd.abcd';

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
