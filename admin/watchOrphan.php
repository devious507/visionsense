<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
	exit();
}
$mac=$_GET['mac'];
$sql="select count(*) from reset_log WHERE mac='{$mac}'";
$res=$db->query($sql);
$row=$res->fetchRow();
$arr[0]="<td>".$row[0]."</td>";
$sql="SELECT tstamp,water,electric,temp1,temp2,temp3,temp4,temp5,temp6,tog1,tog2,tog3,tog4,tog5,tog6 FROM orphans WHERE mac='{$mac}'";
$res=$db->query($sql);
$row=$res->fetchRow();
for($i=0; $i< 15; $i++) {
	if($row[$i] == 't') {
		$arr[]="<td bgcolor=\"green\">{$row[$i]}</td>";
	} elseif($row[$i] == 'f') {
		$arr[]="<td bgcolor=\"red\">{$row[$i]}</td>";
	} else {
		$arr[]="<td>{$row[$i]}</td>";
	}
}

$output="<tr>".implode("",$arr)."</tr>\n";
?>
<!DOCTYPE html>
<html>
<head>
<title>Orphan Watcher</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="3">
</head>
<body>
<p>Sensor Package: <?php echo $mac;?></p>
<table cellpadding="3" cellspacing="0" border="1">
<tr> <td># Reset</td> <td>Timestamp</td> <td>Water</td> <td>Electric</td> <td>Temp 1</td> <td>Temp 2</td> <td>Temp 3</td> <td>Temp 4</td> <td>Temp 5</td> <td>Temp 6</td> <td>Alarm 1</td> <td>Alarm 2</td> <td>Alarm 3</td> <td>Alarm 4</td> <td>Alarm 5</td> <td>Alarm 6</td></tr>
<?php echo $output; ?>
</table>
<p><a href="index.php">Index</a></p>
</body>
</html>
