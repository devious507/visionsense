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

for($i=1; $i<=6; $i++) {
	$sels[]="temp{$i}_lbl";
}
//for($i=1; $i<=6; $i++) {
	//$sels[]="tog{$i}_lbl";
//}
$sql="SELECT ".implode(",",$sels)." FROM sensor_setup WHERE mac='{$mac}'";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
$myItems[]='water';
$myItems[]='electric';
$myLbls[]="Water";
$myLbls[]="Electric";

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
print pageHeader("Sensor History Listing",true,180,7,600);
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">{$description} -- {$time}</td></tr>\n";
$link="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">Sensor-ID: {$link}</td></tr>\n";
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

$sql="SELECT ".implode(",",$myItems)." FROM sensor_log WHERE mac='{$mac}' ORDER BY lastcontact DESC LIMIT {$limit}";
$res=$db->query($sql);
checkDBError($res,$sql);

$waterVal = 99999999;
$waterTotal = 0;
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
	if(($research != 'water') || (($research == 'water') && ($row['water'] != $waterVal))) {
		$waterVal=$row['water'];
		$waterTotal+=$row['water'];
		$count++;
		print "<tr>\n";
		foreach($myItems as $it) {
			switch($it) {
			case "water":
				print minMaxBox(sprintf("%.1f",$row[$it]/$defaults['clickspergal']),$defaults[$it."_min"],$defaults[$it."_max"],'center');
				break;
			case "electric":
				print minMaxBox($row[$it],$defaults[$it."_min"],$defaults[$it."_max"],'center');
				break;
			case "temp1":
			case "temp2":
			case "temp3":
			case "temp4":
			case "temp5":
			case "temp6":
				print minMaxBox($row[$it],$defaults[$it."_min"],$defaults[$it."_max"],'center');
				break;
			case "lastip":
				print "<td align=\"center\">{$row[$it]}</td>";
				break;
			case "lastcontact":
				$t=$row[$it];
				$tt=preg_split("/\./",$t);
				$tt[0]=substr($tt[0],0,strlen($tt[0])-3);
				print "<td align=\"center\">{$tt[0]}</td>";
				break;
			}
		}
		print "</tr>\n";
	}
	$cols=count($row);
}
if($research == 'water2') {
	$cols--;
	print "<tr><td>{$waterTotal}</td><td colspan=\"{$cols}\">&nbsp;</td></tr>\n";
}

print "</table>\n";
print "</body></html>\n";

