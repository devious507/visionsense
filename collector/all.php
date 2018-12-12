<?php

require_once("project.php");

if(!isset($_GET['mac'])) {
	logError('mac','Mac Missing!','unk',$_SERVER['REMOTE_ADDR']);
	exit();
} else {
	$mac=$_GET['mac'];
	$_GET['lastcontact']="now()";
	$_GET['lastip']=$_SERVER['REMOTE_ADDR'];
}

// Check Complete function makes sure all required
// vars are prsent in the query string
checkComplete($_GET,$mac,$_SERVER['REMOTE_ADDR']);

if(DEBUG) {
	logPacket($_SERVER['QUERY_STRING']);
}


$db = connectDB();

// ClicksPerGal == 0 will cause divide by zero errors, if someone set a sensor to 0
// 1 is the more apropriate thing for it to be.  Every sensor update will fix that
// problem for all sensors with the following 3 lines
$sql="UPDATE sensor_setup SET clickspergal=1 WHERE clickspergal=0";
$res=$db->query($sql);
checkDBError($res);

if(isset($_GET['reset']) && ($_GET['reset'] == 'true')) {
	$sql="INSERT INTO reset_log (mac) VALUES ('{$mac}')";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$sql="SELECT water,electric FROM sensor_current WHERE mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	// Reading from first boot is artificially low for water and electric
	// we're gonna fudge the numbers, and use the values from the last sample
	// which is much closer to the truth than 0 to 5 would have been
	$_GET['water']=$row[0];
	$_GET['electric']=$row[1];
	unset($_GET['reset']);		// This causes problems later in the script, undefine it here
}


$sql="SELECT count(mac) as c FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 0) {
	$sql="INSERT INTO orphans VALUES ('{$mac}')";
	$res=$db->query($sql);
	logError('mac',"Sensor Package not configured",$mac,$_SERVER['REMOTE_ADDR']);
	$myStuff=array('water','electric','temp1','temp2','temp3','temp4','temp5','temp6');
	foreach($myStuff as $item) {
		if(isset($_GET[$item])) {
			$sql="UPDATE orphans SET {$item} = '{$_GET[$item]}' WHERE mac='{$mac}'";
			$res=$db->query($sql);
		}
	}
	exit();
}
$sql="SELECT temp1_adj,temp2_adj,temp3_adj,temp4_adj,temp5_adj,temp6_adj FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$adj=$res->fetchRow();



$sql="SELECT count(mac) as c FROM sensor_current WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$row=$res->fetchRow();
if($row[0] == 1) {
	$sql="UPDATE sensor_current SET ";
	unset($_GET['mac']);
	foreach($_GET as $k=>$v) {
		switch($k) {
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			if($v==0) {
				$kvp[]=$k."=false";
			} else {
				$kvp[]=$k.="=true";
			}
			break;
		case "lastip":
			$kvp[]=$k."='".$v."'";
			break;
		case "temp1":
			$v+=$adj[0];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "temp2":
			$v+=$adj[1];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "temp3":
			$v+=$adj[2];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "temp4":
			$v+=$adj[3];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "temp5":
			$v+=$adj[4];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "temp6":
			$v+=$adj[5];
			$kvp[]=$k."=".$v;
			$temp[]=$v;
			break;
		case "water":
			$water=$v;
			$kvp[]=$k."=".$v;
			break;
		case "electric":
			$electric=$v;
			$kvp[]=$k."=".$v;
			break;
		case "reset":
			break;
		default:
			$kvp[]=$k."=".$v;
			break;
		}
	}
	$sql.=implode(", ",$kvp);
	$sql.=" WHERE mac='{$mac}'";
	if(DEBUG) {
		logLine($sql);
	}
	$res=$db->query($sql);
	checkDBError($res,$sql);
} else {
	$sql="INSERT INTO sensor_current (";
	foreach($_GET as $k=>$v) {
		$left[]=$k;
		switch($k) {
		case "mac":
		case "lastcontact":
		case "lastip":
			$right[]="'".$v."'";
			break;
		case "tog1":
		case "tog2":
		case "tog3":
		case "tog4":
		case "tog5":
		case "tog6":
			if($v == 0) {
				$right[]="false";
			} else {
				$right[]="true";
			}
			break;
		case "reset":
			break;
		default:
			$right[]=$v;
			break;
		}
	}
	$sql.=implode(",",$left);
	$sql.=") VALUES (";
	$sql.=implode(",",$right);
	$sql.=")";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	if(DEBUG) {
		logLine('from all.php');
		logLine($sql);
	}
}


$sql="INSERT INTO sensor_log (SELECT mac,water,electric,temp1,temp2,temp3,temp4,temp5,temp6,lastip,lastcontact FROM sensor_current WHERE mac='{$mac}')";
logLine($sql);
$res=$db->query($sql);
checkDBError($res,$sql);
rrdCreate($mac);
rrdUpdate($mac,$temp,$water,$electric);

// Time to parse for any out of range values that
// may require us to email alert... first off, a little hack

$sql="SELECT * FROM email_alerts WHERE active=true and mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
if($res->numRows() <= 0) {
	if(DEBUG) {
		print "No One wants email about this";
	}
	exit(); // All done, no one wants email no reason to do the rest of the tests!
} else {
	if(DEBUG) {
		print "here we go emails!";
	}
}

// Well, someone wanted emails about this sensor package, so now we need to determine what, if any we need to send
// lets start by generating the names of all of the value fields we will ned
$names[]='description';
$names[]='water_min';
$names[]='water_max';
$names[]='electric_min';
$names[]='electric_max';
$names[]='clickspergal';
for($i=1; $i <=6; $i++) {
	$names[]="temp{$i}_min";
	$names[]="temp{$i}_max";
	$names[]="temp{$i}_lbl";
}
for($i=1; $i <=6; $i++) {
	$names[]="tog{$i}";
	$names[]="tog{$i}_lbl";
}
$sql="SELECT ".implode(",",$names)." FROM sensor_setup WHERE mac='{$mac}'";
$res=$db->query($sql);
checkDBError($res,$sql);
$defs=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
$defs['water_lbl'] = 'Water Usage';
$defs['electric_lbl'] = 'Electric Usage';
unset($_GET['lastcontact']);
unset($_GET['lastip']);
foreach($_GET as $k=>$v) {
	if($k == 'water') {
		$v/=$defs['clickspergal'];
	}
	$body = "Attention: your sensor package {$mac} located at/in {$defs['description']} has reported a value that is ";
	$body.= "out of range on the {$defs[$k."_lbl"]} sensor.  As requested this email has been generated to let you know ";
	$body.= "about the issue that may require your attention.\n\n";
	$body.= "This alert can be modified, suspended, or deleted by having your site administrator visit http://my.rtmscloud.com \n\n";
	$body.= "Thank you for your attention in this matter.\n\n";
	if(!preg_match("/^tog/",$k)) {
		$min=$defs[$k."_min"];
		$max=$defs[$k."_max"];
		if($v < $min || $v > $max) {
			//print "{$k}: Alert Value {$v} < {$min}<br>\n";
			$sql="SELECT email FROM email_alerts WHERE active=true AND mac='{$mac}' AND {$k}=true";
			$res=$db->query($sql);
			checkDBError($res,$sql);
			while(($row=$res->fetchRow())==true) {
				$email=$row[0];
				$subject="{$defs['description']} Alert from RTMS";
				vsSendEmail($email,$subject,$body);
				if(DEBUG) {
					print "Sending email for {$defs['description']} ({$mac}) sensor ({$defs[$k."_lbl"]})";
					print " -- {$min} / {$v} / {$max}<br>\n";
				}
			}
		}
	} else {
		$target = $defs[$k];
		if($v == 1) {
			$v='t';
		} else {
			$v='f';
		}
		if($v != $target) {
			$sql="SELECT email FROM email_alerts WHERE active=true AND mac='{$mac}' AND {$k}=true";
			$res=$db->query($sql);
			checkDBError($res,$sql);
			while(($row=$res->fetchRow())==true) {
				$email=$row[0];
				$subject="{$defs['description']} Alert from RTMS";
				vsSendEmail($email,$subject,$body);
			}
			if(DEBUG) {
				print "{$k}: Alert Value {$v} IS NOT {$target}<br>\n";
			}
		}
	}
}

?>
