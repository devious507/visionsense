<?php

// http://www.visionsystems.tv/~paulo/A/
// all.php?mac=abcd.abcd.abcd&water=271&temp1=72&temp2=0&temp3=0&toggle1=1&toggle2=1&toggle3=1&toggle4=1

$macs=array('abcd.abcd.abcd',
	"abcd.abcd.0500",
	"abcd.abcd.0501",
	"abcd.abcd.0502",
	"abcd.abcd.0503",
	"abcd.abcd.0504"
	);
for($i=505; $i<=513; $i++) {
	$macs[]='abcd.abcd.0'.$i;
}
?>
