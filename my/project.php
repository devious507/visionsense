<?php

require_once("MDB2.php");
define("DEBUG",false);
define("RRDTOOL","/usr/bin/rrdtool");
define("ERRORFILE","data/ERRORS");
define("LOGFILE","data/LOG");
define("DSN",'pgsql://paulo@localhost/visionsense');
define("URLBASE","http://collector.rtmscloud.com/");
define("REDHILIGHT","#FF0000");
define("STANDARDHILIGHT","#00FF00");
define("GRAPHDIR","data");
define("PATH","/");
define("DOMAIN","rtmscloud.com");

function pageHeader($title="VisionSense",$table=false,$refresh=0,$cols=100,$width=0,$ss='') {
	if($width >0) {
		$w=" width=\"{$width}\" ";
	}
	$logo = "<img src=\"images/visionSecurityLogo.png\"{$w}>";
	$rv ="<!DOCTYPE html>\n";
	$rv.="<html><head>";
	if($refresh > 0) {
		$rv.="<meta http-equiv=\"refresh\" content=\"{$refresh}\">\n";
	}
	if($ss != '') {
		$rv.=$ss."\n";
	}
	$rv.="<title>{$title}</title></head>\n";
	$rv.="<body>";
	if($table == true) {
		$rv.="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
		$rv.="<tr>\n\t<td bgcolor=\"#cacaca\" colspan=\"{$cols}\"><a href=\"index.php\">{$logo}</a></td>\n</tr>\n";
	} else {
		$rv.="<a href=\"index.php\">{$logo}</a>";
	}
	return $rv;
}
function vsSendEmail($to,$subject,$body) {
	$headers = "From: donotreply@rtmscloud.com\r\n".
		"X-Mailer: PHP/". phpversion();
	mail($to,$subject,$body,$headers);
}
function generateSalt($cryptPW='') {
	if(preg_match("/^\\\$2a\\\$/",$cryptPW)) {
		return substr($cryptPW,0,29);
	} else {
		// Valid Characters for Password Salt (Blowfish Algo)
		$string="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$salty=preg_split("//",$string);
		array_shift($salty);
		array_pop($salty);
		// $2a$ -- Valid Prefix for Blowfish Algo
		$salt='$2a$12$';
		for($i=0; $i < 22; $i++) {
			$r=rand(0,61);
			$salt.=$salty[$r];
		}
		return $salt;
	}
}

function check_password_hash($cryptPW,$PW,$user) {
	$salt = generateSalt($cryptPW);
	$myCryptPW = crypt($PW,$salt);
	if($cryptPW == $myCryptPW) {
		return true;
	}
	return false;
}

function logout() {
	global $_COOKIE;
	global $_POST;
	global $_GET;
	$myVars=array('username','password','ownerID','superadmin');
	foreach($myVars as $v) {
		setcookie($v,'',time()-3600,PATH,DOMAIN);
		setcookie($v,'',time()-3600);
		unset($_POST[$v]);
		unset($_GET[$v]);
		unset($_COOKIE[$v]);
	}
	return;
}

function rangeBox($min,$max) {
	return "\t<td>({$min} - {$max})</td>\n";
}
function expectedBox($val,$openClose=false) {
	if($openClose) {
		if($val == 'f') {
			$val="Open";
		} else {
			$val="Closed";
		}
	}
	return "\t<td>{$val}</td>\n";
}
function matchBox($val,$exp,$openClose=false,$alert="Alert") {
	if($val == '') {
		return "\t<td>&nbsp;</td>\n";
	}
	if($val == $exp) {
		$color=STANDARDHILIGHT;
		$val="OK";
	} else {
		$color=REDHILIGHT;
		$val=$alert;
	}
	/*
	if($openClose) {
		if($val == 'f') {
			$val="Open";
		} else {
			$val="Closed";
		}
	}
	 */
	return "\t<td align=\"center\" bgcolor=\"{$color}\">{$val}</td>\n";
}

function minMaxBox($val,$min,$max,$align='') {
	if(($val < $min) || ($val > $max)) {
		$color=REDHILIGHT;
	} else {
		$color=STANDARDHILIGHT;
	}
	if($align == '') {
		return "\t<td bgcolor=\"{$color}\">{$val}</td>\n";
	} else {
		return "\t<td align=\"{$align}\" bgcolor=\"{$color}\">{$val}</td>\n";
	}
}
function connectDB() {
	$db = MDB2::singleton(DSN);
	if(PEAR::isError($db)) {
		die($db->getMessage());
	} else {
		return $db;
	}
}

function checkDBError($res,$sql=NULL) {
	if(PEAR::isError($res)) {
		logError('DB ERR',$res->getMessage(),'null','null');
		if($sql!=NULL) {
			logError('DB ERR SQL',$sql,'null','null');
		}
		$ta="<textarea cols=\"100\" rows=\"6\">";
		print "<!DOCTYPE html><html><head><title>Error Message</title></head><body>";
		print "<p>A database error has occurred, if this problem persists, please contact support @ 515-222-9997, to report the issue.</p>";
		print "<p>Please be sure to include the date, and time you received this message, as well as what you were doing when this occurred, ";
		print "so that we may research this issue and resolve it for you.</p>";
		print "<p>Timestamp: ".date("m/d/Y H:i:s")."<br>";
		print "Host: {$_SERVER['HTTP_HOST']}<br>";
		print "Script: {$_SERVER['SCRIPT_NAME']}<br>";
		print "Remote-Host: {$_SERVER['REMOTE_ADDR']}<br>";
		if(DEBUG || preg_match("/^172\.16\.0\./",$_SERVER['REMOTE_ADDR'])) {
			$ta="<textarea cols=\"100\" rows=\"4\">";
			print "<hr width=\"725\" align=\"left\">SQL:<br><textarea cols=\"100\" rows=\"5\">{$sql}</textarea>";
			print "<hr width=\"725\" align=\"left\">Error Message: ".$res->getMessage()."<br>";
			print "<hr width=\"725\" align=\"left\">"; print "_GET:<br>{$ta}"; print json_encode($_GET); print "</textarea>"; 
			print "<hr width=\"725\" align=\"left\">"; print "_POST:<br>{$ta}"; print json_encode($_POST); print "</textarea>"; 
			print "<hr width=\"725\" align=\"left\">"; print "_COOKIE:<br>{$ta}"; print json_encode($_COOKIE); print "</textarea>"; 
			print "<hr width=\"725\" align=\"left\">";
		} else {
			print "<hr width=\"725\" align=\"left\">";
			print "Data-1: <br><textarea cols=\"100\" rows=\"5\">".base64_encode(json_encode($_GET))."</textarea>";
			print "<hr width=\"725\" align=\"left\">";
			print "Data-2: <br><textarea cols=\"100\" rows=\"5\">".base64_encode(json_encode($_POST))."</textarea>";
			print "<hr width=\"725\" align=\"left\">";
			print "Data-3: <br><textarea cols=\"100\" rows=\"5\">".base64_encode(json_encode($_COOKIE))."</textarea>";
			print "<hr width=\"725\" align=\"left\">";
		}
		print "</body></html>";
		exit();
	}
}
function logLine($msg) {
	$time = date("m/d/Y H:i:s");
	$message=sprintf("%s:  %s\n",$time,$msg);
	$fh=fopen(LOGFILE,'a');
	fwrite($fh,$message);
	fclose($fh);
}
function logPacket($qs) {
	$time = date("m/d/Y H:i:s");
	$message=sprintf("%s:  %s\n",$time,$qs);
	$fh=fopen(LOGFILE,'a');
	fwrite($fh,$message);
	fclose($fh);
}

function checkComplete($get,$mac,$ip) {
	//header("Content-type: text/plain"); var_dump($get);
	$myVars = array("mac","water","electric","temp1","temp2","temp3","temp4","temp5","temp6"
	);
	foreach($myVars as $v) {
		if(!isset($get[$v])) {
			print "Error: All required information not supplied!";
			logError($v,"Missing Required Value From",$mac,$ip);
			exit();
		}
	}
}

function logError($var,$mesg,$mac,$ip) {
	$time = date('m/d/Y H:i:s');
	$message=sprintf("%s -- %s: %s (%s) %s\n",$time,$var,$mesg,$mac,$ip);
	$fh=fopen(ERRORFILE,'a');
	fwrite($fh,$message);
	fclose($fh);
}
function rrdUpdate($mac,$t,$w,$e) {
	$filename="data/rrd/{$mac}.rrd";
	if(file_exists($filename)) {
		$myCmd = RRDTOOL." update {$filename} N:";
		foreach($t as $temp) {
			$myCmd.="{$temp}:";
		}
		$myCmd.="{$w}:";
		$myCmd.="{$e} ";
	} else {
		logError("RRDUPDATE","unable to update {$filename}","null","null");
	}
	if(DEBUG) {
		logLine($myCmd);
	}
	shell_exec($myCmd);
}
function rrdCreate($mac) {
	$createCMD=RRDTOOL." create %s \
		--step '300' \
		'DS:temp1:GAUGE:600:-20:220' \
		'DS:temp2:GAUGE:600:-20:220' \
		'DS:temp3:GAUGE:600:-20:220' \
		'DS:temp4:GAUGE:600:-20:220' \
		'DS:temp5:GAUGE:600:-20:220' \
		'DS:temp6:GAUGE:600:-20:220' \
		'DS:water:GAUGE:600:0:5000' \
		'DS:electricity:GAUGE:600:0:1000' \
		'RRA:AVERAGE:0.5:1:2016' \
		'RRA:MIN:0.5:1:2016' \
		'RRA:MAX:0.5:1:2016' \
		'RRA:AVERAGE:0.5:6:1440' \
		'RRA:MIN:0.5:6:1440' \
		'RRA:MAX:0.5:6:1440' \
		'RRA:AVERAGE:0.5:12:1440' \
		'RRA:MIN:0.5:12:1440' \
		'RRA:MAX:0.5:12:1440' \
		'RRA:AVERAGE:0.5:288:365' \
		'RRA:MIN:0.5:288:365' \
		'RRA:MAX:0.5:288:365'";
	$filename="data/rrd/{$mac}.rrd";
	$myCMD=sprintf($createCMD,$filename);
	if(file_exists($filename)) {
		return;
	}
	if(!is_dir('data/rrd')) {
		mkdir('data/rrd');
		if(!is_dir('data/rrd')) {
			logError("FILEOP","Unable to create data/rrd","null","null");
			return;
		}
	}
	chmod('data/rrd',0777);
	shell_exec($myCMD);
	if(!file_exists($filename)) {
		logError("FILEOP","Failed to create {$filename}","null","null");
	}
	return;
}

function generateGraph($mac,$id,$link=false,$period=null,$waterFactor=1) {
	$db=connectDB();
	$sql="SELECT description FROM sensor_setup WHERE mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	$description = $row[0];
	$sql="SELECT mac,h_lbl,v_lbl,width,height,timeframe FROM graph_master WHERE id={$id} AND mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$info=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
	$tempFile=tempnam(GRAPHDIR,$mac);
	$rrdCmd=RRDTOOL." graph '{$tempFile}.png' ";
	if($info['width'] == '') {
		$width=400;
	} else {
		$width=$info['width'];
	}
	if($info['height'] == '') {
		$height = 100;
	} else {
		$height = $info['height'];
	}
	if(isset($period)) {
		// do nothing, all is good
	} elseif($info['timeframe'] != '') {
		$period=$info['timeframe'];
	} else {
		$period='1d';
	}
	if($info['h_lbl'] != '') {
		switch($period) {
		case '1h':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 1 Hour' ";
			break;
		case '2h':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 2 Hours' ";
			break;
		case '6h':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 6 Hours' ";
			break;
		case '12h':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 12 Hours' ";
			break;
		case '1d':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 1 Day' ";
			break;
		case '1w':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 1 Week' ";
			break;
		case '1m':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 1 Month' ";
			break;
		case '1y':
			$rrdCmd .="--title '{$description}: {$info['h_lbl']} -- 1 Year' ";
			break;
		default:
			$rrdCmd .="--title '{$description}: {$info['h_lbl']}' ";
			break;
		}
	}
	if($info['v_lbl'] != '') {
		$rrdCmd .="--vertical-label '{$info['v_lbl']}' ";
	}
	$rrdCmd .= "--width {$width} ";
	$rrdCmd .= "--height {$height} ";
	$rrdCmd .="--start end-{$period} ";
	unlink($tempFile);
	$tempFile.=".png";
	$sql="SELECT clickspergal FROM sensor_setup WHERE mac='{$mac}'";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$row=$res->fetchRow();
	$waterFactor=$row[0];
	$sql="SELECT * FROM graph_items WHERE graphid={$id} ORDER BY id";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	$count=1;
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
		if($row['color'] == '') {
			$color="#000000";
		} else {
			$color=$row['color'];
		}
		$defs[]="'DEF:d{$count}=data/rrd/{$mac}.rrd:{$row['col_name']}:AVERAGE' ";
		//$defs[]="'VDEF:d{$count}=d{$count},MIN' ";
		if($row['col_name'] == 'water') {
			$defs[]="'CDEF:d{$count}{$count}=d{$count},{$waterFactor},/' ";
			$line[]="'{$row['type']}:d{$count}{$count}{$color}:{$row['col_lbl']}' ";
		} else {
			$line[]="'{$row['type']}:d{$count}{$color}:{$row['col_lbl']}' ";
			//$line[]="'GPRINT:d{$count}min:%d' ";
		}
		$count++;
	}
	foreach($defs as $d) {
		$rrdCmd.=$d;
	}
	foreach($line as $l) {
		$rrdCmd.=$l;
	}
	$rrdCmd.=" --watermark 'Copyright 2018 Vision Systems LLC. All Rights Reserved'";
	shell_exec($rrdCmd);
	$image = base64_encode(file_get_contents($tempFile));
	unlink($tempFile);

	//return $rrdCmd;
	if($link) {
		return "<a href=\"graphList.php?mac={$mac}&id={$id}\"><img src=\"data:image/png;base64,{$image}\"></a>";
	} else {
		return "<img src=\"data:image/png;base64,{$image}\">";
	}

}

function getBuildingGroupById($mac,$cur) {
	$db = connectDB();
	$sql="SELECT owner FROM sensor_setup WHERE mac='{$mac}'";
	$res = $db->query($sql);
	checkDBError($res);
	$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	$ownerID=$row['owner'];
	$sql="SELECT id,group_name FROM sensor_groups WHERE owner_id={$ownerID} ORDER BY group_name ASC";
	$res=$db->query($sql);
	checkDBError($res);
	$rv="<select name=\"sensor_group\">\n";
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
		$id=$row['id'];
		$name = $row['group_name'];
		$selected="";
		if($id == $cur) {
			$selected=" selected=\"selected\"";
		}
		$rv.="\t<option value=\"{$id}\"{$selected}>{$name}</option>\n";
	}
	$rv.="</select>\n";
	return $rv;
}

function debugDumper($a) {
	print "<pre>";
	var_dump($a);
	print "</pre>";
	exit();
}
?>
