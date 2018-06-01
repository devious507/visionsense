<?php

require_once("project.php");
require_once("security.php");


if(isset($_GET['mac'])) {
	$mac=$_GET['mac'];
} else{
	header("Location: index.php");
	exit();
}

$db=connectDB();




$res=$db->query("SELECT * FROM sensor_setup WHERE mac='{$mac}'");
checkDBError($res,$sql);
$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
if($_COOKIE['superadmin'] == 't') {
	$sql="SELECT userid,username,email FROM users ORDER BY username,email";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$ownerSel="<tr><td>Owner</td><td colspan=\"4\"><select name=\"owner\">";
	while(($row2=$res->fetchRow())==true) {
		$selected='';
		if($row2[0] == $row['owner']) {
			$selected=" selected=\"selected\"";
		}
		$ownerSel.="<option value=\"{$row2[0]}\"{$selected}>{$row2[1]} | {$row2[2]}</option>";
	}
	$ownerSel.="</option></td></tr>";
}
unset($row['mac']);
unset($row['owner']);

ob_start(); var_dump($row); $debug="<pre>".ob_get_contents()."</pre>"; ob_end_clean();

$hidden="<input type=\"hidden\" name=\"mac\" value=\"{$mac}\">\n";

$table="<tr><td bgcolor=\"#cacaca\" colspan=\"5\"><b>Sensor-ID:</b> {$mac}</td></tr>\n";
$table.="<tr><td bgcolor=\"#cacaca\" colspan=\"5\"><a href=\"sensorDetail.php?mac={$mac}\">Back</a> || <a href=\"graphManagement.php?mac={$mac}\">Graphs</a> || <a href=\"emailAlerts.php?mac={$mac}\">Emails</a></td></tr>\n";
if($_COOKIE['superadmin'] == 't') {
	$table.=$ownerSel;
}
$table.="<tr><td>Address</td><td colspan=\"4\"><input type=\"text\" name=\"description\" value=\"{$row['description']}\" size=\"25\"></td></tr>\n";
$bGroup=getBuildingGroupById($mac,$row['sensor_group']);
$table.="<tr><td>Group</td><td colspan=\"4\">{$bGroup}</td></tr>\n";
$table.="<tr><td>&nbsp;</td><td>Min</td><td colspan=\"1\">Max</td><td>Clicks / Gallon</td><td>Alert Hours</td></tr>\n";
$table.="<tr><td>Water</td><td><input type=\"text\" name=\"water_min\" value=\"{$row['water_min']}\" size=\"5\"></td><td colspan=\"1\"><input type=\"text\" size=\"5\" name=\"water_max\" value=\"{$row['water_max']}\"></td><td><input type=\"text\" size=\"2\" name=\"clickspergal\" value=\"{$row['clickspergal']}\"></td><td><input type=\"text\" size=\"5\" name=\"wateralerthours\" value=\"{$row['wateralerthours']}\"></td></tr>\n";
//$table.="<tr><td>&nbsp;</td><td>Min</td><td colspan=\"1\">Max</td><td>Adj. Factor</td></tr>\n";
$table.="<tr><td>&nbsp;</td><td>Min</td><td colspan=\"1\">Max</td><td>Current Factor</td><td>&nbsp;</td></tr>\n";
$table.="<tr><td>Electric</td><td><input type=\"text\" name=\"electric_min\" value=\"{$row['electric_min']}\" size=\"5\"></td><td colspan=\"1\"><input type=\"text\" size=\"5\" name=\"electric_max\" value=\"{$row['electric_max']}\"></td><td><input type=\"text\" size=\"5\" name=\"rmsadjust\" value=\"{$row['rmsadjust']}\"></td><td>&nbsp;</td></tr>\n";
//$table.="<tr><td>Electric</td><td><input type=\"text\" name=\"electric_min\" value=\"{$row['electric_min']}\" size=\"5\"></td><td colspan=\"1\"><input type=\"text\" size=\"5\" name=\"electric_max\" value=\"{$row['electric_max']}\"></td><td>&nbsp;</td></tr>\n";
$table.="<tr><td>&nbsp;</td><td>Min</td><td>Max</td><td>Label</td><td>Adjustment</td></tr>\n";
for($i=1; $i<=6; $i++) {
	$s=3;
	$ss=10;
	$lbl=$row['temp'.$i.'_lbl'];
	$min=$row['temp'.$i.'_min'];
	$max=$row['temp'.$i.'_max'];
	$adj=$row['temp'.$i.'_adj'];
	// Limit temp sensors to 2 for now
	$table.="<tr><td>Temp {$i}</td><td><input type=\"text=\" size=\"{$s}\" name=\"temp{$i}_min\" value=\"{$min}\"></td><td><input type=\"text\" size=\"{$s}\" name=\"temp{$i}_max\" value=\"{$max}\"></td><td><input type=\"text\" size=\"{$ss}\" name=\"temp{$i}_lbl\" value=\"{$lbl}\"></td><td><input type=\"text\" size=\"{$s}\" name=\"temp{$i}_adj\" value=\"{$adj}\"></td></tr>\n";
}
for($i=1; $i<=6; $i++) {
	$lbl="Int. {$i}";
	$val=$row['tog'.$i];
	$key='tog'.$i;
	if($val == 'f') {
		$ov="<select name=\"{$key}\"><option value=\"f\" selected=\"selected\">Open</option><option value=\"t\">Closed</option></select>";
	} else {
		$ov="<select name=\"{$key}\"><option value=\"f\">Open</option><option value=\"t\" selected=\"selected\">Closed</option></select>";
	}
	$tlbl='tog'.$i."_lbl";
	$table.="<tr><td>{$lbl}</td><td colspan=\"2\">{$ov}</td><td colspan=\"2\"><input type=\"text\" name=\"{$tlbl}\" value=\"{$row[$tlbl]}\" size=\"{$ss}\"></td></tr>\n";
}

$table.="<tr><td colspan=\"5\"><input type=\"submit\"></td></tr>\n";
if(DEBUG) {
	$table.="<tr><td colspan=\"5\">{$debug}</td></tr>\n";
} 

print pageHeader("Edit Building",false,0,5,400);
?>
<form method="post" action="editBuildingAction.php">
<?php echo $hidden; ?>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $table; ?>
</table>
</form>
</body>
</html>
