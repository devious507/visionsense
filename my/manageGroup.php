<?php

require_once("project.php");
require_once("security.php");

$db=connectDB();
$sql="SELECT id,group_name FROM sensor_groups WHERE immutable=false AND owner_id={$_COOKIE['ownerID']}";
$res=$db->query($sql);
checkDBError($res,$sql);
$tData="<tr><td bgcolor=\"#cacaca\">Display Group</td></tr>\n";
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$tData.="<tr>\n";
	$img="<img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\">";
	$link="<a href=\"editGroup.php?id={$row['id']}\">{$row['group_name']}</a>";
	$dele="<a href=\"deleteGroup.php?id={$row['id']}\">{$img}</a>";
	$tData.="<td>{$link}&nbsp;&nbsp;{$dele}</td>";
	$tData.="</tr>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>My Groups</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $tData; ?>
</table>
<p><a href="index.php">Back to List</a><br>
<a href="createGroup.php">Create New Group</a></p>
</body>
</html>
