<?php

require_once("project.php");
require_once("security.php");

$needed = array("mac","id");
foreach($needed as $n) {
	if(!isset($_GET[$n])) {
		header("Location: index.php");
	}
}

$mac=$_GET['mac'];
$id =$_GET['id'];
$db =connectDB();
$sql1 = "DELETE FROM graph_items WHERE graphid={$id}";
$sql2 = "DELETE FROM graph_master WHERE id={$id}";

$res=$db->query($sql1);
checkDBError($res);

$res=$db->query($sql2);
checkDBError($res);

header("Location: graphManagement.php?mac={$mac}");
?>
