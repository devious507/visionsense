<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['mac']) || !isset($_GET['id'])) {
	header("Location: index.php");
} else {
	$mac=$_GET['mac'];
	$id=$_GET['id'];
	$hidden_id="<input type=\"hidden\" name=\"id\" value=\"{$id}\">\n";
	$hidden_id.="<input type=\"hidden\" name=\"mac\" value=\"{$mac}\">\n";
}

$db=connectDB();
$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$address=$row[0];

$sql="SELECT * FROM graph_master WHERE mac='{$mac}' AND id={$id}";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);

$link="<a href=\"graphManagement.php?mac={$mac}\">{$mac}</a>";
$tData ="<tr><td colspan=\"2\" bgcolor=\"#cacaca\">{$address}</td></tr>\n";
$tData.="<tr><td colspan=\"2\" bgcolor=\"#cacaca\">{$link} - {$id} Graph Setup</td></tr>\n";
$tData.="<tr><td align=\"left\" valign=\"top\">---ELEMENTS---</td><td align=\"left\" valign=\"top\">---MASTER---</td></tr>\n";

$t1 ="<form method=\"post\" action=\"editGraphPropertiesAction.php\">\n";
$t1.=$hidden_id;
$t1.="<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">\n";
$myRows=array('h_lbl'=>"Horizontal Label",
	'v_lbl'=>"Vertical Label",
	'width'=>"Width",
	'height'=>"Height",
	'sortorder'=>"Graph Order");

foreach($myRows as $k=>$v) {
	switch($k) {
	case "sortorder":
		$size=2;
		break;
	case "h_lbl":
		$size=20;
		break;
	default:
		$size=20;
		break;
	}
	$t1.="<tr><td>{$v}</td><td><input type=\"text\" size=\"{$size}\" name=\"$k\" value=\"{$row[$k]}\"></td></tr>\n";
}
$t1.="<tr><td>Graph Timeframe</td><td><select name=\"timeframe\">";
$myFrames=array('1h','2h','6h','12h','1d','1w','1m','1y');
foreach($myFrames as $f) {
	if($row['timeframe'] == $f) {
		$t1.="<option value=\"{$f}\" selected=\"selected\">{$f}</option>";
	} else {
		$t1.="<option value=\"{$f}\">{$f}</option>";
	}
}
$t1.="</select></td></tr>\n";
$t1.="<tr><td colspan=\"2\"><input type=\"submit\"></td></tr>\n";
$t1.="</table>\n";
$t1.="</form>\n";

$sql="SELECT id,col_name,col_lbl,color,type FROM graph_items WHERE graphid={$id} ORDER BY id";
$res=$db->query($sql);
checkDBError($res,$sql);
if($res->numRows() > 0) {
	$t2="<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
	$t2.="<tr><td>Label</td><td>Color</td><td colspan=\"2\">Type</td></tr>\n";
} else {
	$t2='';
}
$myCols='';
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$types=array("LINE1"=>"Line ",
		"LINE2"=>"Line 2",
		"LINE4"=>"Line 3",
		"LINE6"=>"Line 4",
		"AREA"=>"Filled Area");
	$type="<select name=\"type\">";
	foreach($types as $k=>$v) {
		if($k == $row['type']) {
			$selected=" selected=\"selected\"";
		} else {
			$selected="";
		}
		$type.="<option value=\"{$k}\"{$selected}>{$v}</option>";
	}
	$type.="</select>";

	$del="<a href=\"deleteGraphItem.php?id={$row['id']}&mac={$mac}&graphid={$id}\"><img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\"></a>";
	$t2.="<form action=\"updateGraphElement.php\" method=\"post\">\n";
	$t2.="<input type=\"hidden\" name=\"graphid\" value=\"{$id}\">\n";
	$t2.="<input type=\"hidden\" name=\"id\" value=\"{$row['id']}\">\n";
	$t2.="<input type=\"hidden\" name=\"mac\" value=\"{$mac}\">\n";
	$t2.="<tr><td><input type=\"text\" size=\"6\" name=\"col_lbl\" value=\"{$row['col_lbl']}\"></td><td><input type=\"color\" size=\"6\" name=\"color\" value=\"{$row['color']}\"></td><td>{$type}</td><td><input type=\"submit\" value=\"update\"> {$del}</td></tr>\n";
	$t2.="</form>\n";
	$myCols[$row['col_name']]=true;
}

if($res->numRows() > 0) {
	$t2.="</table>\n";
}
$sql="SELECT g.mac,s.temp1_lbl,s.temp2_lbl,s.temp3_lbl,s.temp4_lbl,s.temp5_lbl,s.temp6_lbl FROM graph_master AS g LEFT OUTER JOIN sensor_setup AS s on g.mac=s.mac WHERE g.id={$id}";
$res=$db->query($sql);
checkDBError($res);
$links='';
$extras=array('water','electricity');
foreach($extras as $k) {
		$myLbl=$k;
		$col_name=$k;
		$links[]="<li><a href=\"addGraphElement.php?mac={$mac}&graphid={$id}&col_name={$col_name}&col_lbl={$myLbl}\">{$myLbl}</a></li>";
}
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	for($i=1; $i<=6; $i++) {
			$myLbl=$row['temp'.$i.'_lbl'];
			if($myLbl != '') {
				$col_name="temp{$i}";
				$links[]="<li><a href=\"addGraphElement.php?mac={$mac}&graphid={$id}&col_name={$col_name}&col_lbl={$myLbl}\">{$myLbl}</a></li>";
			}
	}
}
$t2.="<ul>".implode("",$links)."</ul>";


$tData=preg_replace("/---MASTER---/",$t1,$tData);
$tData=preg_replace("/---ELEMENTS---/",$t2,$tData);
$tData.="<tr><td colspan=\"3\">".generateGraph($mac,$id)."</td></tr>\n";




print pageHeader("Edit Graph Properties",true,0,2,700);
?>
<?php echo $tData; ?>
</table>
</body>
</html>
