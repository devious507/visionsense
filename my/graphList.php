<?php

require_once("project.php");
require_oncE("security.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
} 
if(!isset($_GET['id'])) {
	header("Location: index.php");
}

$mac=$_GET['mac'];
$id=$_GET['id'];
$tdata ="<tr><td>".generateGraph($mac,$id,false,'1h')."</td>";
$tdata.="<td>".generateGraph($mac,$id,false,'2h')."</td></tr>";

$tdata.="<tr><td>".generateGraph($mac,$id,false,'6h')."</td>";
$tdata.="<td>".generateGraph($mac,$id,false,'12h')."</td></tr>";

$tdata.="<tr><td>".generateGraph($mac,$id,false,'1d')."</td>";
$tdata.="<td>".generateGraph($mac,$id,false,'1w')."</td></tr>";

$tdata.="<tr><td>".generateGraph($mac,$id,false,'1m')."</td>";
$tdata.="<td>".generateGraph($mac,$id,false,'1y')."</td></tr>";

$db=connectDB();
$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
$bID=$row[0]." -- ".date('H:i:s m/d/Y');
$sID="<a href=\"sensorDetail.php?mac={$mac}\">{$mac}</a>";
?>
<!DOCTYPE html>
<html>
<head>
<title>Graph List <?php echo $mac;?></title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" bgcolor="#cacaca"><?php echo $bID; ?></td></tr>
<tr><td colspan="2" bgcolor="#cacaca">Sensor-ID: <?php echo $sID; ?></td></tr>
<?php echo $tdata; ?>
</table>
</body>
</html>
