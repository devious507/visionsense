<?php

$goodCode = "2SHD5USTa6Fv";
if(isset($_GET['code']) && $_GET['code'] == $goodCode) {
	require_once("project.php");
	$db = connectDB();
	$sql = "SELECT mac_addr FROM mac_library WHERE tstamp IS NULL ORDER BY id ASC LIMIT 1";
	$sth = $db->query($sql);
	$row = $sth->fetchRow();
	header("Content-type: text/plain");
	print $row[0];
	$sql = "UPDATE mac_library SET tstamp=now() WHERE mac_addr = '{$row[0]}'";
	$sth = $db->query($sql);
	exit();
} else {
	die();
}
?>
