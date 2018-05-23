<?php

require_once("project.php");
require_once("security.php");


if(isset($_GET['mac'])) {
	$mac=$_GET['mac'];
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
$myLbls[]='IP';
$myItems[]='lastcontact';
$myLbls[]='TimeStamp';

$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$description=$row[0];
$d_colspan=count($myLbls);
$time=date("H:i:s m/d/Y");

print pageHeader("Smart Building Detail",true,60,$d_colspan);
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">{$description} -- {$time}</td></tr>\n";
print "<tr><td bgcolor=\"#cacaca\" colspan=\"{$d_colspan}\">Sensor-ID: {$mac}</td></tr>\n";
print "<tr>\n";
foreach($myLbls as $txt) {
	print "\t<td align=\"center\">{$txt}</td>\n";
}
print "</tr>\n";

$sql="SELECT ".implode(",",$myItems)." FROM sensor_current WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
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
				print rangeBox($defaults[$it."_min"],$defaults[$it."_max"],'center');
				break;
			case "tog1":
			case "tog2":
			case "tog3":
			case "tog4":
			case "tog5":
			case "tog6":
				print expectedBox($defaults[$it],true,'center');
				break;
			case "lastip":
			case "lastcontact":
				print "<td>&nbsp;</td>\n";
				break;
			}
		}
		print "</tr>\n";
	}
	print "<tr>\n";
	foreach($myItems as $it) {
		switch($it) {
		case "water":
			print minMaxBox(sprintf("%.1f",$row[$it]/$defaults['clickspergal']),$defaults[$it."_min"],$defaults[$it."_max"],'center');
			break;
		case "electric":
		case "temp1":
		case "temp2":
		case "temp3":
		case "temp4":
		case "temp5":
		case "temp6":
			print minMaxBox($row[$it],$defaults[$it."_min"],$defaults[$it."_max"],'center');
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
			$then=strtotime($row[$it]);
			$age=time()-$then;
			if(($age >= 600) && ($it == 'lastcontact') ) {
				$bg="bgcolor=\"".REDHIGHLIGHT."\"";
			} else {
				$bg="";
			}
			print "<td {$bg} align=\"center\">{$row[$it]}</td>\n";
			break;
		}
	}
	print "</tr>\n";
}

print "<tr><td colspan=\"16\"><a href=\"sensorList.php\">Full List</a> || <a href=\"historyMenu.php?mac={$mac}&lines=10\">History Menu</a> || <a href=\"editBuilding.php?mac={$mac}\">Edit</a></td></tr>\n";

$end=false;
$sql="SELECT id FROM graph_master WHERE mac='{$mac}' ORDER BY sortorder ASC";
$res=$db->query($sql);
checkDBError($res,$sql);
while(($row=$res->fetchRow())==true) {
	if($end) {
		print generateGraph($mac,$row[0],true)."</td></tr>\n";
	} else {
		print "<tr><td colspan=\"6\">".generateGraph($mac,$row[0],true)."</td><td colspan=\"5\">";
	}
	if($end) {
		$end=false;
	} else {
		$end=true;
	}
}
if($end) {
	print "&nbsp;</td></tr>\n";
}


print "</table></body></html>\n";
?>
