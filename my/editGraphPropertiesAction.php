<?php

require_once("project.php");
require_once("security.php");

$needs=array('id','mac','h_lbl','v_lbl','width','height','timeframe');
foreach($needs as $n) {
	if(!isset($_POST[$n])) {
		header("Location: index.php");
	}
}
$id=$_POST['id'];
unset($_POST['id']);
$mac=$_POST['mac'];
unset($_POST['mac']);
foreach($_POST as $k=>$v) {
	switch($k) {
	case "width":
	case "height":
		$kvp[]=$k."=".$v;
		break;
	default:
		$kvp[]=$k."='".$v."'";
		break;
	}
}
$sql="UPDATE graph_master SET ".implode(", ",$kvp)." WHERE id={$id}";
$db=connectDB();
$res=$db->query($sql);
checkDBError($res,$sql);
var_dump($_POST);
header("Location: editGraph.php?mac={$mac}&id={$id}");
?>
