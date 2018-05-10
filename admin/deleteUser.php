<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

if(!isset($_GET['userid'])) {
	header("Location: userManager.php");
} else {
	$id=$_GET['userid'];
	$db = connectDB();
	$sql="SELECT username,email FROM users WHERE userid={$id}";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	$nom="({$row[0]}) -- {$row[1]}";
}
if(isset($_GET['confirm']) && ($_GET['confirm'] == 'true')) {
	$db = connectDB();
	$sql="UPDATE sensor_setup SET owner=1,sensor_group=1 WHERE owner='{$id}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$sql="DELETE FROM sensor_groups WHERE owner_id={$id}";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$sql="DELETE FROM users WHERE userid={$id}";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	header("Location: userManager.php");
	exit();
}

$link="<a href=\"deleteUser.php?userid={$id}&confirm=true\">Confirm Delete User {$nom}</a>";
?>
<!DOCTYPE html>
<html>
<head>
<title>Delete User Step 1</title>
<head>
<body>
<?php echo $link; ?>
</body>
</html>
