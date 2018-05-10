<?php

require_once("project.php");
require_once("security.php");

$db=connectDB();
$sql="INSERT INTO sensor_groups (owner_id,group_name) VALUES ({$_COOKIE['ownerID']},'Newly Created Group')";
$res=$db->query($sql);
checkDBError($res,$sql);

header("Location: manageGroup.php");
?>
