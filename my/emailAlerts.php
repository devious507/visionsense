<?php

require_once("project.php");
require_once("security.php");

$mac=NULL;
if(!isset($_GET['mac'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
}

$db = connectDB();
$sql="SELECT temp1_lbl,temp2_lbl,temp3_lbl,temp4_lbl,temp5_lbl,temp6_lbl,tog1_lbl,tog2_lbl,tog3_lbl,tog4_lbl,tog5_lbl,tog6_lbl FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res);
$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
$bg="bgcolor=\"#cacaca\"";
$cl="class=\"rotate\"";
$tmp=array("Send Emails","Sensor Down","Elec. Usage","Water Usage");
$header="<tr>";
$header.="<th {$bg} class=\"left_side\">Email Address</th>";
foreach($tmp as $t) {
	$header.="<th {$cl}><div><span>{$t}</span></div></th>";
}
unset($th);
$ks[]='id';
$ks[]='mac';
$ks[]='email';
$ks[]='active';
$ks[]='sensor_down';
$ks[]='water';
$ks[]='electric';
$cols=4;
foreach($row as $k=>$v) {
	if($v != '') {
		$header.="<th {$cl}><div><span>{$v}</span></div></th>";
		$cols++;
		$kk=preg_replace("/_lbl/","",$k);
		$ks[]=$kk;
	}
}
//$cols++;
$header.="<th {$bg} class=\"right_side\">&nbsp;</th>";
$header.="</tr>";
$table='';
$sql="SELECT ".implode(", ",$ks)." FROM email_alerts WHERE mac='{$mac}' ORDER BY email,mac";
$res=$db->query($sql);
checkDBError();
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$table.="<form method=\"post\" action=\"updateEmails.php\">";
	$table.="<input type=\"hidden\" name=\"id\" value=\"{$row['id']}\">";
	$table.="<input type=\"hidden\" name=\"mac\" value=\"{$mac}\">";
	$table.="<tr>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "id":
		case "mac":
			break;
		case "email":
			$table.="<td><input type=\"text\" name=\"email\" value=\"{$v}\"></td>";
			break;
		default:
			if($v == 't') {
				$ch=" checked=\"checked\"";
			} else {
				$ch='';
			}
			$table.="<td><input type=\"checkbox\" name=\"{$k}\"{$ch}></td>";
			break;
		}
	}
	$table.="<td><input type=\"submit\" value=\"Update\"></td>";
	$table.="</tr></form>\n";
}

$cc=$cols+1;
$ccc=$cc+1;
$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$address=$row[0];
$table_header ="<tr><th {$bg} align=\"left\" colspan=\"{$ccc}\">{$address}</th></tr>\n";
$table_header.="<tr><th {$bg} align=\"left\" colspan=\"{$cc}\"><b>Sensor-ID: {$mac} </b>&nbsp;&nbsp;&nbsp;<a href=\"addEmail.php?mac={$mac}\">Add New Email</a></th><th {$bg} align=\"center\"><a href=\"editBuilding.php?mac={$mac}\">Back</a></th></tr>\n";
if($table == "") {
	$header="";
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Email Alerting Setup</title>
<link REL="STYLESHEET" TYPE="text/css" HREF="rotate.css">
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $table_header; ?>
<?php echo $header; ?>
<?php echo $table; ?>
</table>
</body>
</html>
