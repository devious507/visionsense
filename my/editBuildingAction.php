<?php

require_once("project.php");
require_once("security.php");

//print "<pre>"; var_dump($_POST); print "</pre>";

$needed=array("mac","description","water_min","water_max","electric_min","electric_max");
for($i=1; $i<=2; $i++) {
	$needed[]="temp{$i}_min";
	$needed[]="temp{$i}_max";
	$needed[]="temp{$i}_lbl";
}
for($i=1; $i<=6; $i++) {
	$needed[]="tog{$i}";
	$needed[]="tog{$i}_lbl";
}

//print "<pre>"; var_dump($needed); print "</pre>";
foreach($needed as $n) {
	if(!isset($_POST[$n])) {
		print $n;
		exit();
	}
}

$mac=$_POST['mac'];
unset($_POST['mac']);
foreach($_POST as $k=>$v) {
	switch($k) {
	case "description":
	case "temp1_lbl":
	case "temp2_lbl":
	case "temp3_lbl":
	case "temp4_lbl":
	case "temp5_lbl":
	case "temp6_lbl":
	case "tog1_lbl":
	case "tog2_lbl":
	case "tog3_lbl":
	case "tog4_lbl":
	case "tog5_lbl":
	case "tog6_lbl":
		$kvp[]=$k."='".addslashes($v)."'";
		break;
	case "tog1":
	case "tog2":
	case "tog3":
	case "tog4":
	case "tog5":
	case "tog6":
		if($v == 't') {
			$kvp[]=$k."=true";
		} else {
			$kvp[]=$k."=false";
		}
		break;
	default:
		$kvp[]=$k."=".$v;
		break;
	}
}
$sql="UPDATE sensor_setup SET ";
$sql.=implode(", ",$kvp);
$sql.=" WHERE mac='{$mac}'";
print $sql;
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
header("Location: sensorList.php?mac={$mac}");
