<?php

require_once("project.php");
require_oncE("security.php");

$needed=array("id","mac","graphid");
foreach($needed as $n) {
	if(!isset($_GET[$n])) {
		header("Location: index.php");
	}
}

$sql="DELETE FROM graph_items WHERE id={$_GET['id']}";
$location="editGraph.php?mac={$_GET['mac']}&id={$_GET['graphid']}";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res);
header("Location: {$location}");
?>
