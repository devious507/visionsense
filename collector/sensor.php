<?php

require_once("project.php");

if(!isset($_GET['mac'])) {
	logError('mac','Mac Missing!','unk',$_SERVER['REMOTE_ADDR']);
	exit();
} else {
	$mac=$_GET['mac'];
	$_GET['lastcontact']="now()";
	$_GET['lastip']=$_SERVER['REMOTE_ADDR'];
}

if(DEBUG) {
	logPacket($_SERVER['QUERY_STRING']);
}
if(!isset($_GET['mac'])) {
	logError("Communication","Sensors not configured",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}
if(!isset($_GET['sensor'])) {
	logError("Communication","Sensor # Not Present",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}
if(!isset($_GET['value'])) {
	logError("Communication","Sensor Value Not Present",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}


$db = connectDB();
$sql="SELECT count(mac) as c FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 0) {
	$sql="UPDATE orphans SET tog{$_GET['sensor']}='{$_GET['value']}' WHERE mac='{$mac}'";
	$res=$db->query($sql);
	logError('mac',"Sensor Package not configured",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}
$sql="SELECT count(mac) as c FROM sensor_current WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 1) {
	$sensor=$_GET['sensor'];
	$sensor="tog".$sensor;
	$value=$_GET['value'];
	if($value == 0) {
		$value='false';
	} else {
		$value='true';
	}
	$sql="UPDATE sensor_current SET {$sensor}={$value}, lastip='{$_SERVER['REMOTE_ADDR']}', lastcontact=now() WHERE mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$sql="INSERT INTO alarm_log (mac,{$sensor},lastip,lastcontact) VALUES ('{$mac}','{$value}','{$_SERVER['REMOTE_ADDR']}',now())";
	$res=$db->query($sql);
	checkDBError($res,$sql);
} else {
	logError("System","Unable to Update values initial readings not present",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}
if($row[0] == 0) {
}

?>
