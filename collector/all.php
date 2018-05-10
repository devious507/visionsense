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

// Check Complete function makes sure all required
// vars are prsent in the query string
checkComplete($_GET,$mac,$_SERVER['REMOTE_ADDR']);

if(DEBUG) {
	logPacket($_SERVER['QUERY_STRING']);
}


$db = connectDB();

$sql="SELECT count(mac) as c FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 0) {
	$sql="INSERT INTO orphans VALUES ('{$mac}')";
	$res=$db->query($sql);
	logError('mac',"Sensor Package not configured",$mac,$_SERVER['REMOTE_ADDR']);
	exit();
}
$sql="SELECT count(mac) as c FROM sensor_current WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 1) {
	$sql="UPDATE sensor_current SET ";
	unset($_GET['mac']);
	foreach($_GET as $k=>$v) {
		switch($k) {
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			if($v==0) {
				$kvp[]=$k."=false";
			} else {
				$kvp[]=$k.="=true";
			}
			break;
		case "lastip":
			$kvp[]=$k."='".$v."'";
			break;
		case "temp1":
		case "temp2":
		case "temp3":
		case "temp4":
		case "temp5":
		case "temp6":
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "water":
			$water=$v;
			$kvp[]=$k."=".$v;
			break;
		case "electric":
			$electric=$v;
			$kvp[]=$k."=".$v;
			break;
		default:
			$kvp[]=$k."=".$v;
			break;
		}
	}
	$sql.=implode(", ",$kvp);
	$sql.=" WHERE mac='{$mac}'";
	if(DEBUG) {
		logLine($sql);
	}
	$res=$db->query($sql);
	checkDBError($res,$sql);
} else {
	$sql="INSERT INTO sensor_current (";
	foreach($_GET as $k=>$v) {
		$left[]=$k;
		switch($k) {
		case "mac":
		case "lastcontact":
		case "lastip":
			$right[]="'".$v."'";
			break;
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			if($v == 0) {
				$right[]="false";
			} else {
				$right[]="true";
			}
			break;
		default:
			$right[]=$v;
			break;
		}
	}
	$sql.=implode(",",$left);
	$sql.=") VALUES (";
	$sql.=implode(",",$right);
	$sql.=")";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	if(DEBUG) {
		logLine('from all.php');
		logLine($sql);
	}
}


$sql="INSERT INTO sensor_log (SELECT * FROM sensor_current WHERE mac='{$mac}')";
logLine($sql);
$res=$db->query($sql);
checkDBError($res,$sql);
rrdCreate($mac);
rrdUpdate($mac,$temp,$water,$electric);


?>
