<?php

exit();
require_once("project.php");
define("ALERT_AGE",300);
define("FILE_AGE",3600);
define("TOUCH_FILE","data/watchDogDave.txt");
define("PHONENUM",'17012405449');
define("PHONENUM2",'17012402815');
define("PHONENUM3",'15152295620');

$db=connectDB();
$sql="SELECT lastcontact FROM sensor_current WHERE mac='0200.0001.0004'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();

$now=time();
$then=strtotime($row[0]);
$age = $now-$then;
 
print pageHeader("WatchDog Dave!",true,30,11);
print "<tr><td>Now:</td><td colspan=\"10\">{$now}</td></tr>";
print "<tr><td>Then:</td><td colspan=\"10\">{$then}</td></tr>";
print "<tr><td>Age:</td><td colspan=\"10\">{$age}</td></tr>";

$sql="SELECT count(*) FROM sms_response";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
print "<tr><td>SMS Lines:</td><td colspan=\"10\">{$row[0]}</td></tr>";

$sql="select * from sms_response ORDER BY receive_time DESC LIMIT 10";
$res=$db->query($sql);
checkDBError($res);
$header=true;
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	unset($row['_from']);
	unset($row['id']);
	unset($row['_parentmessageuuid']);
	$t=preg_split("/\./",$row['receive_time']);
	$row['receive_time']=$t[0]; //.".".substr($t[1],0,5);
	if($header) {
		print "<tr>";
		foreach($row as $k=>$v) {
			print "\t<td bgcolor=\"#cacaca\">{$k}</td>\n";
		}
		print "</tr>\n";
		$header=false;
	}
	print "<tr>";
	foreach($row as $k=>$v) {
		print "\t<td>{$v}</td>\n";
	}
	print "</tr>\n";
}


print "</table></body></html>";
if($age < ALERT_AGE) {
	if(file_exists(TOUCH_FILE)) {
		unlink(TOUCH_FILE);
	}
	exit();
}
if(!file_exists(TOUCH_FILE)) {
	touch(TOUCH_FILE);
	sendSMS(PHONENUM,'5951 Vista Dr -- Sensors Offline '.date("h:i m/d/Y"));
	sleep(2);
	sendSMS(PHONENUM3,'5951 Vista Dr -- Sensors Offline '.date("h:i m/d/Y"));
	if(date('G') >=8 && date('G') < 22) {
		sleep(2);
		sendSMS(PHONENUM2,'5951 Vista Dr -- Sensors Offline '.date("h:i m/d/Y"));
	}
	exit();
}
$file_mtime = filemtime(TOUCH_FILE);
$file_age = $now-$file_mtime;

if($file_age < FILE_AGE) {
	exit();
}
touch(TOUCH_FILE);
sendSMS(PHONENUM,'5951 Vista Dr -- Sensors Offline '.date("h:i a m/d/Y"));
