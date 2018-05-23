<?php

require_once("project.php");
require_once("security.php");

$valids = array("Boiler Temp","Water","Electrical");
if(isset($_GET['group'])) {
	$valid=false;
	$graph=$_GET['group'];
	foreach($valids as $v) {
		if($graph == $v) {
			$valid=true;
		}
	}
	if($valid == false) {
		displayList();
	}
} else {
	displayList();
}
$ownerID = $_COOKIE['ownerID'];
if(isset($_GET['building_group'])) {
	$building_group = $_GET['building_group'];
	$sql="SELECT id FROM sensor_groups WHERE owner_id='{$ownerID}' AND group_name='{$building_group}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	$groupID=$row[0];
	$extra='"'.$building_group.'"';
}
if(isset($groupID)) {
	$grp="AND s.sensor_group={$groupID}";
} else {
	$grp="";
}
if(!isset($extra)) {
	$extra='';
}
$sql ="SELECT s.mac,g.id FROM sensor_setup AS s LEFT OUTER JOIN graph_master ";
$sql.="AS g ON s.mac=g.mac WHERE owner={$ownerID} AND h_lbl='{$graph}' {$grp} ORDER BY s.description";
$res=$db->query($sql);
checkDBError($res,$sql);
$end=false;
$time=date('m/d/Y h:i a');
print pageHeader($graph." Graphs",true,300,2);
print "<tr><td bgcolor=\"#cacaca\" colspan=\"2\">{$_GET['group']} Graphs -- {$extra} Generateed {$time}</td></tr>";

while(($row=$res->fetchRow())==true) {
	if($end) {
		print generateGraph($row[0],$row[1],true)."</td></tr>\n";
	} else {
		print "<tr><td>".generateGraph($row[0],$row[1],true)."</td><td>";
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
print "</table></body></html>";

function displayList() {
	print "<!DOCTYPE html><html><head><title>Graph Groups</title></head><body>";
	print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
	print "<tr><td bgcolor=\"#cacaca\">Available Groups</td></tr>";
	print "<tr><td><a href=\"graphGroups.php?group=Boiler%20Temp\">Boiler Temps</a></td></tr>";
	print "<tr><td><a href=\"graphGroups.php?group=Water\">Water</a></td></tr>";
	print "<tr><td><a href=\"graphGroups.php?group=Electrical\">Electrical</a></td></tr>";
	print "</table></body></html>";
	exit();
}
?>
