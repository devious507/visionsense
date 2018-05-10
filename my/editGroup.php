<?php

require_once("project.php");
require_once("security.php");

if(!isset($_GET['id'])) {
	header("Location: index.php");
} else {
	$id=$_GET['id'];
}

$db=connectDB();
$sql="SELECT owner_id,group_name FROM sensor_groups WHERE id={$id}";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] != $_COOKIE['ownerID']) {
	header("Location: index.php");
}
$input="<input type=\"hidden\" name=\"id\" value=\"{$id}\">";
$input.="<input type=\"text\" name=\"group_name\" value=\"{$row[1]}\">";
?>
<!DOCTYPE html>
<html>
<head>
<title>Editing Group Name</title>
</head>
<body>
<form method="POST" action="editGroupAction.php">
<table cellpadding="5" cellspacing="0" border="1">
<tr><td bgcolor="#cacaca">Group Name</td></tr>
<tr><td><?php echo $input; ?></td></tr>
<tr><td><input type="submit" value="Update"></td></tr>
</table>
</form>
</body>
</html>
