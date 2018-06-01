<?php

require_once("project.php");
require_once("security.php");


if(isset($_GET['mac'])) {
	header("Location: sensorDetail.php?mac={$_GET['mac']}");
	exit();
} else {
	$sql="SELECT description,mac,water,electric,temp1,temp2,temp3,temp4,temp5,temp6,tog1,tog2,tog3,tog4,tog5,tog6,lastcontact,sensor_group FROM view_display WHERE owner={$_COOKIE['ownerID']} ORDER BY sensor_group,description,mac";
}

$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);

print pageHeader("Smart Building List",true,180,16);
$bg="bgcolor=\"#ececec\"";
$center="align=\"center\"";
$header='';
$header.="<tr>\n";
$header.="\t<td {$bg}>Description</td>\n";
$header.="\t<td {$center} {$bg}>Water</td>\n";
$header.="\t<td {$center} {$bg}>Electric</td>\n";
$header.="\t<td {$bg} colspan=\"6\">Temperatures</td>\n";
$header.="\t<td {$bg} colspan=\"6\">Alerts</td>\n";
$header.="</tr>\n";

$mygroup="boogity";
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC)) == true) {
	$now=time();
	$then=strtotime($row['lastcontact']);
	$age=$now-$then;
	$sql="SELECT * FROM sensor_setup WHERE mac='{$row['mac']}'";
	$res2=$db->query($sql);
	checkDBError($res2,$sql);
	$defaults=$res2->fetchRow(MDB2_FETCHMODE_ASSOC);
	if($defaults['sensor_group'] != $mygroup) {
		$sql3 = "SELECT group_name FROM sensor_groups WHERE id='{$defaults['sensor_group']}'";
		$res3=$db->query($sql3);
		checkDBError($res3);
		$row3=$res3->fetchRow();
		$group_name=$row3[0];
		$boilers="<a href=\"graphGroups.php?group=Boiler Temp&building_group={$group_name}\">Boiler Graphs</a>";
		$water ="<a href=\"graphGroups.php?group=Water&building_group={$group_name}\">Water Graphs</a>";
		$electrical = "<a href=\"graphGroups.php?group=Electrical&building_group={$group_name}\">Electrical Graphs</a>";
		print "<tr><td bgcolor=\"#cacaca\" colspan=\"16\"><hr><b>{$group_name}:</b> {$boilers}, {$water}, {$electrical}<hr></td></tr>\n";
		$mygroup=$defaults['sensor_group'];
		print $header;
	}
	if($row['description'] == '') {
		$row['description'] = $row['mac'];
	}
	print "<tr>\n";
	if($age >= 600) {
		$bg=" bgcolor=\"".REDHIGHLIGHT."\"";
	}
	print "\t<td {$bg}><a href=\"sensorDetail.php?mac={$row['mac']}\">{$row['description']}</td>";
	
	// Need to do some calcs here
	// 1.  What is the wateralerthours value
	// 2.  Has there beein a 0 within the last $value amount of time?
	$sqlAlert = "SELECT mac from sensor_log WHERE mac='{$row['mac']}' AND water <= {$defaults['water_min']} AND lastcontact > now()-interval '{$defaults['wateralerthours']} hours' ORDER BY lastcontact DESC LIMIT 1";
	$resAlert = $db->query($sqlAlert);
	checkDBError($resAlert,$sqlAlert);
	if($resAlert->numRows() == 0) {
		print "<td align=\"center\" bgcolor=\"".REDHIGHLIGHT."\">".sprintf("%.1f",$row['water']/$defaults['clickspergal'])."</td>";
	} else {
		print minMaxBox(sprintf("%.1f",$row['water']/$defaults['clickspergal']),$defaults['water_min'],$defaults['water_max'],'center');
	}

	print minMaxBox($row['electric'],$defaults['electric_min'],$defaults['electric_max'],'center');

	for($i=1; $i<=6; $i++) {
		$txt = 'temp'.$i;
		$min = $txt."_min";
		$max = $txt."_max";
		$lbl = $txt."_lbl";
		if(strlen($defaults[$lbl]) > 0) {
			print minMaxBox($row[$txt],$defaults[$min],$defaults[$max],'center');
		} else {
			print "<td bgcolor=\"#e3ea93\">&nbsp;</td>";
		}
	}
	for($i=1; $i<=6; $i++) {
		$txt = 'tog'.$i;
		$lbl = $txt."_lbl";
		if(strlen($defaults[$lbl]) > 0) {
			print matchBox($row[$txt],$defaults[$txt],true,$defaults[$lbl]);
		} else {
			print "<td bgcolor=\"#e3ea93\">&nbsp;</td>";
		}
	}
	print "</tr>\n";
}

if(isset($_GET['mac'])) {
	print "<tr><td colspan=\"16\"><a href=\"sensorList.php\">Full List</a> || <a href=\"sensorHistory.php?mac={$mac}&lines=10\">History</a> || <a href=\"editBuilding.php?mac={$mac}\">Edit</a></td></tr>\n";
	print "<tr><td colspan=\"16\">RRD Graphs go here</td></tr>\n";
}
print "<tr><td colspan=\"16\"><a href=\"manageGroup.php\">My Groups</a> || <a href=\"logout.php\">Logout</a> || <a href=\"graphGroups.php\">Graph Groups</a></td></tr>\n";
print "</table>\n";
print "</body></html>\n";

