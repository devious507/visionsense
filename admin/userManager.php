<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

$db=connectDB();
$sql="SELECT userid,username,email,superadmin FROM users ORDER BY username,email";
$res=$db->query($sql);
checkDBError($res,$sql);
$userTable='';
while(($row=$res->fetchRow())==true) {
	if($row[3] != 't') {
		$icon="<img width=\"16\" height=\"16\" src=\"images/icons/delete-8x.png\">";
		$delLink="<a href=\"/deleteUser.php?userid={$row[0]}\">{$icon}</a>";
	} else {
		$delLink='';
	}
	$link="<a href=\"editUser.php?userid={$row[0]}\">{$row[1]}</a> {$delLink}";
	$userTable.="<tr><td>{$row[0]}</td><td>{$link}</td><td>{$row[2]}</td><td>{$row[3]}</td></tr>";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>User List</title>
</head>
<body>
<p><a href="index.php">Back</a> || <a href="createUser.php">New User</a></p>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td bgcolor="#cacaca">ID #</td><td bgcolor="#cacaca">Username</td><td bgcolor="#cacaca">Email</td><td bgcolor="#cacaca">Superadmin</td></tr>
<?php echo $userTable; ?>
</table>
</body>
</html>
