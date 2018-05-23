<?php

require_once("project.php");
require_once("security.php");


if(isset($_GET['mac'])) {
	$mac=$_GET['mac'];
}

if(!isset($_GET['lines'])) {
	$limit = 10;
} else {
	$limit = $_GET['lines'];
}
if(isset($_GET['mode']) && $_GET['mode'] == 'water_research') {
	$research='water';
} else {
	$research='normal';
}

//for($i=1; $i<=6; $i++) {
	//$sels[]="temp{$i}_lbl";
//}
for($i=1; $i<=6; $i++) {
	$sels[]="tog{$i}_lbl";
}
$sql="SELECT ".implode(",",$sels)." FROM sensor_setup WHERE mac='{$mac}'";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);

while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	foreach($row as $k=>$v) {
		if(($v!='') && ($v!='null') && ($v!=null)) {
			$tmp=preg_split("/_/",$k);
			$myItems[]=$tmp[0];
			$myLbls[]=$v;
		}
	}
}
$myItems[]='lastip';
$myLbls[]="IP";
$myItems[]="lastcontact";
$myLbls[]="TimeStamp";

$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$description=$row[0];
$d_colspan=count($myLbls);
$time=date("H:i:s m/d/Y");

$waterRearchLink="<a href=\"sensorHistory.php?mac={$mac}&lines=1000&research=water_research\">Water Usage Research</a>";
$lineCounts=array(10,25,50,100,250,500,1000);
foreach($lineCounts as $l) {
	$links[]="<a href=\"alarmHistory.php?mac={$mac}&lines={$l}\">{$l}</a>";
}
$linkArray = implode(" | ",$links);
print pageHeader("Alarm Logs",true,180,6,600);
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">{$description} -- {$time}</td></tr>\n";
$link="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">Sensor-ID: {$link}<hr> # Lines: {$linkArray}</td></tr>\n";
print "<tr>\n";
foreach($myLbls as $txt) {
	print "\t<td align=\"center\">{$txt}</td>\n";
}
print "</tr>\n";


$sql="SELECT * FROM sensor_setup WHERE mac='{$mac}'";
$res2=$db->query($sql);
checkDBError($res2,$sql);
$defaults=$res2->fetchRow(MDB2_FETCHMODE_ASSOC);
if(DEBUG) {
	print "<tr>\n";
	foreach($myItems as $it) {
		switch($it) {
		case "water":
		case "electric":
		case "temp1":
		case "temp2":
		case "temp3":
		case "temp4":
		case "temp5":
		case "temp6":
			print rangeBox($defaults[$it."_min"],$defaults[$it."_max"]);
			break;
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			print expectedBox($defaults[$it],true);
			break;
		case "lastip":
		case "lastcontact":
			print "<td>&nbsp;</td>\n";
		}
	}
	print "</tr>\n";
}

$sql="SELECT ".implode(",",$myItems)." FROM alarm_log WHERE mac='{$mac}' ORDER BY lastcontact DESC LIMIT {$limit}";
$res=$db->query($sql);
checkDBError($res,$sql);

$waterVal = 99999999;
$waterTotal = 0;
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
	$doit=false;
	foreach($myItems as $it) {
		switch($it) {
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			if($row[$it] != '') {
				$doit=true;
			}
			break;
		default:
			break;
		}
	}
	if($doit == true) {
		print "<tr>\n";
		foreach($myItems as $it) {
			switch($it) {
			case "tog1":
			case "tog2":
			case "tog3":
			case "tog4":
			case "tog5":
			case "tog6":
				print matchBox($row[$it],$defaults[$it],true);
				break;

			case "lastip":
				print "<td align=\"center\">{$row[$it]}</td>";
				break;
			case "lastcontact":
				$t=preg_split("/\./",$row[$it]);
				print "<td align=\"center\">{$t[0]}</td>";
				break;
			}
		}
		print "</tr>\n";
	}
}
if($research == 'water2') {
	$cols--;
	print "<tr><td>{$waterTotal}</td><td colspan=\"{$cols}\">&nbsp;</td></tr>\n";
}

print "</table>\n";
print "</body></html>\n";

