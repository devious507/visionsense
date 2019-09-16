<?php

require_once("project.php");
// Used by admin.rtmscloud.com/index.php

function getRow($mac) {
	$db=connectDB();
	$sql=getSQL($mac);
	$res=$db->query($sql);
	checkDBError($res);
	$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
	return buildTR($row);
}

function fixWater($ma,$wa) {
	$db=connectDB();
	$sql="SELECT clickspergal FROM sensor_setup WHERE mac='{$ma}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	return sprintf("%.1f",round($wa/$row[0],1));
}
function buildTR($row) {
	$row['water']=fixWater($row['mac'],$row['water']);
	foreach($row as $k=>$v) {
		if(preg_match("/^temp/",$k)) {
			$colors[$k]=getTempColor($row['mac'],$k,$v);
		} elseif ($k == 'water') {
			$colors[$k]=getTempColor($row['mac'],$k,$v);
		} elseif ($k == 'electric') {
			$colors[$k]=getTempColor($row['mac'],$k,$v);
		}
		if($v == "NULL") {
			$cells[]="<td>&nbsp;</td>";
		} elseif($k == 'mac') {
			$del="<a href=\"deleteSensor.php?mac={$v}\"><img src=\"/images/icons/delete-8x.png\" width=\"16\" height=\"16\"></a>";
			$cells[]="<td><a href=\"http://my.rtmscloud.com/sensorDetail.php?mac={$v}\">{$v}</a> {$del}</td>";
		} else {
			if(isset($colors[$k])) {
				$bg=" ".$colors[$k];
			} else {
				$bg="";
			}
			$cells[]="<td{$bg}>{$v}</td>";
		}
	}
	return "<tr>".implode("",$cells)."</tr>";
}

function getTempColor($mac,$field,$value) {
	$sql="SELECT {$field}_min,{$field}_max FROM sensor_setup WHERE mac='{$mac}'";
	$db=connectDB();
	$res=$db->query($sql);
	checkDBError($res);
	$row=$res->fetchRow();
	$min=$row[0];
	$max=$row[1];
	if(($min <= $value) && ($value <= $max)) {
		return "bgcolor=\"green\"";
	} else {
		return "bgcolor=\"red\"";
	}
}

function getSQL($mac) {
	$sql="SELECT s.mac,u.email,s.description,s.temp1_lbl ,s.temp2_lbl ,s.temp3_lbl ,s.temp4_lbl ,s.temp5_lbl ,s.temp6_lbl FROM sensor_setup AS s LEFT OUTER JOIN users AS u ON s.owner=u.userid WHERE s.mac='{$mac}'";
	$db=connectDB();
	$res=$db->query($sql);
	checkDBError($res);
	$order=array("NULL as n1","NULL as n2","NULL as n3","NULL as n4","NULL as n5","NULL as n6");
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
		$order[labelSwitch($row['temp1_lbl'])] = 'temp1';
		$order[labelSwitch($row['temp2_lbl'])] = 'temp2';
		$order[labelSwitch($row['temp3_lbl'])] = 'temp3';
		$order[labelSwitch($row['temp4_lbl'])] = 'temp4';
		$order[labelSwitch($row['temp5_lbl'])] = 'temp5';
		$order[labelSwitch($row['temp6_lbl'])] = 'temp6';
		$email=$row['email'];
		$desc=$row['description'];
	}
	unset($order[7]);
	$temps=implode(", ",$order);
	$sql="SELECT '{$mac}' as mac,'{$email}' as email, '{$desc}' as description, water, electric, {$temps} FROM sensor_current WHERE mac='{$mac}'";
	return $sql;
}

function labelSwitch($in) {
	switch($in) {
	case "Supply":
		return 0;
		break;
	case "Return":
		return 1;
		break;
	case "Supply2":
		return 2;
		break;
	case "Return2":
		return 3;
		break;
	case "Hot Water":
		return 4;
		break;
	case "Room":
		return 5;
		break;
	default:
		return 7;
		break;
	}
}

function doDump($var) {
	print "<pre>";
	var_dump($var);
	print "</pre>";
}
