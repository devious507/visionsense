<?php

// http://www.visionsystems.tv/~paulo/A/
// all.php?mac=abcd.abcd.abcd&water=271&temp1=72&temp2=0&temp3=0&toggle1=1&toggle2=1&toggle3=1&toggle4=1
//
//

require_once("project.php");

$mac='abcd.abcd.abcd';
$sensor=rand(5,8);

$dice=rand(1,100);
if($dice <=80) {
	exit();
} else {
	$sensorNames[5]='1';
	$sensorNames[6]='2';
	$sensorNames[7]='3';
	$sensorNames[8]='4';
	$db=connectDB();
	$sql=sprintf("SELECT %s FROM sensor_current WHERE mac='%s'",$sensorNames[$sensor],$mac);
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	$value=$row[0];
	if($value == 't') {
		$value=0;
	} else {
		$value=1;
	}
	$qs=sprintf("sensor.php?mac=%s&sensor=%s&value=%d",$mac,$sensorNames[$sensor],$value);
	$url=URLBASE.$qs;
	print $url;
	$fh=fopen($url,'r');
	$contents=stream_get_contents($fh);
	fclose($fh);
}
