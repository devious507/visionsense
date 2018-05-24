<?php

require_once("project.php");

foreach($_POST as $k=>$v) {
	$left[]="_".$k;
	$right[]=addslashes($v);
}
$lefts=implode(", ",$left);
$rights=implode("', '",$right);
$sql="INSERT INTO sms_response ({$lefts}) VALUES ('{$rights}')";


$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
?>
