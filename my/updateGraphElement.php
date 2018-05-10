<?php

require_once("project.php");
require_once("security.php");

$needed=array("mac","graphid","id","col_lbl","color","type");
foreach($needed as $n) {
	if(!isset($_POST[$n])) {
		header("Location: index.php");
	}
}

$mac=$_POST['mac'];
$graphid=$_POST['graphid'];
$lbl=$_POST['col_lbl'];
$color=$_POST['color'];
$id=$_POST['id'];
$type=$_POST['type'];
$sql="UPDATE graph_items SET col_lbl='{$lbl}', color='{$color}', type='{$type}' WHERE id={$id}";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res);

$location="editGraph.php?mac={$mac}&id={$graphid}";
header("Location: {$location}");
?>
