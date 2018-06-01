<?php

// http://www.visionsystems.tv/~paulo/A/
// all.php?mac=abcd.abcd.abcd&water=271&temp1=72&temp2=0&temp3=0&toggle1=1&toggle2=1&toggle3=1&toggle4=1

require_once("project.php");
require_once("fakerLib.php");

foreach($macs as $mac) {
	if(rand(0,100) > 90) {
		$water=0;
	} else {
		$water=rand(1,720);		// 1/12 to 60 Gallons
	}
	$temp1=rand(170,180);
	$temp2=rand(80,100);
	$temp3=$temp1+rand(5,10);
	$temp4=rand(70,74);
	$temp5=rand(70,74);
	$temp6=rand(70,74);
	$electric=rand(100,350);
	if($temp1 > $temp3) {
		$holdVar=$temp1;
		$temp1=$temp3;
		$temp3=$temp1;
	}

	$qs=sprintf("all.php?mac=%s&water=%d&electric=%d&temp1=%d&temp2=%d&temp3=%d&temp4=%d&temp5=%d&temp6=%d",
		$mac,
		$water,
		$electric,
		$temp1,
		$temp2,
		$temp3,
		$temp4,
		$temp5,
		$temp6
	);

	$fullURL=URLBASE.$qs;
	print $fullURL;
	print "<br>";
	$fh=fopen($fullURL,'r');
	$data=stream_get_contents($fh);
	fclose($fh);
}
