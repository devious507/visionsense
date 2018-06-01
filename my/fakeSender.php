<?php

// http://www.visionsystems.tv/~paulo/A/
// all.php?mac=abcd.abcd.abcd&water=271&temp1=72&temp2=0&temp3=0&toggle1=1&toggle2=1&toggle3=1&toggle4=1
//
//

require_once("project.php");
require_once("fakerLib.php");

foreach($macs as $mac) {
	for($i=1; $i<=4; $i++) {
		$dice=rand(0,100);
		if($dice >= 95) {
			$val=0;
		} else {
			$val=1;
		}
		if($i == 1) {
			if($val == 1) {
				$val=0;
			} else {
				$val=1;
			}
		}
		$url=sprintf("http://collector.rtmscloud.com/sensor.php?mac=%s&sensor=%d&value=%d",$mac,$i,$val);
		print $url."<br>";
		$fh=fopen($url,'r');
		$data=stream_get_contents($fh);
		fclose($fh);
	}
}
?>
