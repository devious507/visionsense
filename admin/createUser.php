<?php

require_once("project.php");
require_once("security.php");
require_once("adminSecurity.php");

$num=rand(100000,999999);
$username="aaa".$num;
$password="*******";
$sql="INSERT INTO users (username,password,email) VALUES ('{$username}','$password','devnull@visionsystems.tv')";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
header("Location: userManager.php");
?>
