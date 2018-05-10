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

for($i=1; $i<=6; $i++) {
	$sels[]="temp{$i}_lbl";
}
for($i=1; $i<=6; $i++) {
	$sels[]="tog{$i}_lbl";
}
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

print "<!DOCTYPE html>\n<html><head><meta http-equiv=\"refresh\" content=\"30\"><title>Smart Building List</title></head><body>\n";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">{$description} -- {$time}</td></tr>\n";
$link="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">Sensor-ID: {$link}</td></tr>\n";
print "<tr>\n";
foreach($myLbls as $txt) {
	print "\t<td>{$txt}</td>\n";
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

while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
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
			print minMaxBox($row[$it],$defaults[$it."_min"],$defaults[$it."_max"]);
			break;
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			print matchBox($row[$it],$defaults[$it],true);
			break;

		case "lastip":
		case "lastcontact":
			print "<td>{$row[$it]}</td>";
			break;
		}
	}
	print "</tr>\n";
}

print "</table>\n";
print "</body></html>\n";

