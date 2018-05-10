<?php

require_once("project.php");
require_once("security.php");

if(!isset($_POST['id'])) {
	header("Location: index.php");
} else {
	$id=$_POST['id'];
	unset($_POST['id']);
}
if(!isset($_POST['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_POST['mac'];
	unset($_POST['mac']);
}

$pieces=array("active","sensor_down","water","electric","temp1","temp2","temp3",
	"temp4","temp5","temp6","tog1","tog2","tog3",
	"tog4","tog5","tog6");

foreach($pieces as $p) {
	$pp[]="{$p}=false";
}
unset($pieces);
$sql="UPDATE email_alerts SET ".implode(", ",$pp)." WHERE id={$id}";
unset($pp);

foreach($_POST as $k=>$v) {
	switch($k) {
	case "email":
		$pp[]="{$k}='{$v}'";
		break;
	default:
		$pp[]="{$k}=true";
		break;
	}
}
$db=connectDB();
if($_POST['email'] != '') {
	$sql2="UPDATE email_alerts SET ".implode(", ",$pp)." WHERE id={$id}";
	$db=connectDB();
	$res=$db->query($sql);
	checkDBError($res);
	$res=$db->query($sql2);
	checkDBError($res);
} else {
	$sql="DELETE FROM email_alerts WHERE id={$id}";
	$res=$db->query($sql);
	checkDBError($res);
}
header("Location: emailAlerts.php?mac={$mac}");
?>
