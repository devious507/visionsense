<?php

// http://www.visionsystems.tv/~paulo/A/
// all.php?mac=abcd.abcd.abcd&water=271&temp1=72&temp2=0&temp3=0&toggle1=1&toggle2=1&toggle3=1&toggle4=1

require_once("project.php");
$mac='abcd.abcd.abcd';
$water=rand(0,2000);
if($water < 1500) {
	$water=0;
}
$temp1=rand(90,170);
$temp2=rand(90,170);
$temp3=rand(60,85);
$temp4=rand(90,170);
$temp5=rand(90,170);
$temp6=rand(90,170);
$toggle1=1;
$toggle2=1;
$toggle3=1;
$toggle4=1;
$toggle5=1;
$toggle6=1;
/*
$toggle1=rand(0,100);
if($toggle1 <= 10) {
	$toggle1=0;
} else {
	$toggle1=1;
}
$toggle2=rand(0,100);
if($toggle2 <= 10) {
	$toggle2=0; 
} else {
	$toggle2=1;
}
$toggle3=rand(0,100);
if($toggle3 <= 10) {
	$toggle3=0;
} else {
	$toggle3=1;
}
$toggle4=rand(0,100);
if($toggle4 <= 10) {
	$toggle4=0;
} else {
	$toggle4=1;
}
$toggle5=rand(0,100);
if($toggle5 <= 10) {
	$toggle5=0;
} else {
	$toggle5=1;
}
$toggle6=rand(0,100);
if($toggle6 <= 10) {
	$toggle6=0;
} else {
	$toggle6=1;
}
 */
$electric=rand(100,1000);

$qs=sprintf("all.php?mac=%s&water=%d&electric=%d&temp1=%d&temp2=%d&temp3=%d&temp4=%d&temp5=%d&temp6=%d&tog1=%d&tog2=%d&tog3=%d&tog4=%d&tog5=%d&tog6=%d",
	$mac,
	$water,
	$electric,
	$temp1,
	$temp2,
	$temp3,
	$temp4,
	$temp5,
	$temp6,
	$toggle1,
	$toggle2,
	$toggle3,
	$toggle4,
	$toggle5,
	$toggle6
);

$fullURL=URLBASE.$qs;
print $fullURL;
$fh=fopen($fullURL,'r');
$data=stream_get_contents($fh);
fclose($fh);
