<?php

require_once("project.php");
require_once("security.php");
$needed=array("graphid","col_name","col_lbl","mac");
foreach($needed as $n) {
	if(!isset($_GET[$n])) {
		header("Location: index.php");
	} else {
		$id=$_GET['graphid'];
		$mac=$_GET['mac'];
		$name=$_GET['col_name'];
		$lbl=$_GET['col_lbl'];
		$color="#000000";
	}
}

$sql="INSERT INTO graph_items (graphid,col_name,col_lbl,color) VALUES ({$id},'{$name}','{$lbl}','{$color}')";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);

$url="editGraph.php?mac={$mac}&id={$id}";
header("Location: {$url}");
