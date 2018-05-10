<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

$req=array("Muserid","Musername","Mpassword","Memail");
foreach($req as $r) {
	if(!isset($_POST[$r])) {
		header("Location: index.php");
	}
}
$id = $_POST['Muserid'];
unset($_POST['Muserid']);
$sql="UPDATE users SET ";
foreach($_POST as $k=>$v) {
	$k=preg_replace("/M/","",$k);
	if(($k == 'password') && (!preg_match("/^\\\$2a\\\$/",$v))) {
		$mSalt=generateSalt();
		$mCrypt=crypt($v,$mSalt);
		$v=$mCrypt;
		$kvp[]="{$k}='{$v}'";
	} elseif($k=='superadmin') {
		$kvp[]="superadmin=true";
	} else {
		$kvp[]="{$k}='{$v}'";
	}
}
if((!isset($_POST['Msuperadmin'])) && ($id != 1)) {
	$kvp[]="superadmin=false";
}
$sql.=implode(", ",$kvp);
$sql.=" WHERE userid={$id}";

$db = connectDB();
$res = $db->query($sql);
checkDBError($res,$sql);
header("Location: userManager.php");
?>
