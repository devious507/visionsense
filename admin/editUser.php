<?php

if(!isset($_GET['userid'])) {
	header("Location: userManager.php");
} else {
	$user = $_GET['userid'];
}
require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

$db = connectDB();
$sql="SELECT * FROM users WHERE userid={$user}";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
$tbl="<tr>";
foreach($row as $k=>$v) {
	switch($k) {
	case "userid":
		$tbl.="<td><input type=\"hidden\" name=\"M{$k}\" value=\"{$v}\">{$v}</td>";
		break;
	case "password":
		$tbl.="<td><input type=\"password\" name=\"M{$k}\" value=\"{$v}\"></td>";
		break;
	case "superadmin":
		if($v == 't') {
			$checked=" checked=\"checked\"";
		} else {
			$checked='';
		}
		$tbl.="<td><input type=\"checkbox\" name=\"M{$k}\"{$checked}></td>";
		break;
	default:
		$tbl.="<td><input type=\"text\" name=\"M{$k}\" value=\"{$v}\"></td>";
		break;
	}
}
$tbl.="</tr>\n";
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
</head>
<body>
<form method="post" action="editUserAction.php">
<table cellpadding="5" cellspacing="0" border="1">
<tr><td bgcolor="#cacaca" colspan="5"><b>Editing User</b></td></tr>
<tr><td bgcolor="#cacaca">UserID</td><td bgcolor="#cacaca">UserName</td><td bgcolor="#cacaca">Password</td><td bgcolor="#cacaca">E-Mail</td><td bgcolor="#cacaca">SuperAdmin</td></tr>
<?php echo $tbl; ?>
<tr><td colspan="5"><input type="submit" value="Update User"></td></tr>
</table>
</form>
</body>
</html>
