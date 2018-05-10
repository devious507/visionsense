<?php

require_once("project.php");

$referer = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
if(!validateLogin()) {
	doLoginBox();
} else {
	$db =connectDB();
	$sql="SELECT * FROM sensor_groups WHERE owner_id={$_COOKIE['ownerID']}";
	$res=$db->query($sql);
	checkDBError($res,$sql);
	if($res->numRows() == 0) {
$sql="INSERT INTO sensor_groups (owner_id,group_name,immutable) VALUES ({$_COOKIE['ownerID']},' Default',true)";
		$res=$db->query($sql);
		checkDBError($res,$sql);
	}
}


function validateLogin($referer="http://my.rtmscloud.com") {
	$securitySQL="SELECT * FROM users WHERE username='%s'";
	global $_COOKIE;
	global $_GET;
	global $_POST;
	// Lets find the Username and Password
	// Prefer POST, then GET, then COOKIE
	if(isset($_POST['username'])) {
		$username=$_POST['username'];
	} elseif(isset($_GET['username'])) {
		$username=$_GET['username'];
	} elseif(isset($_COOKIE['username'])) {
		$username=$_COOKIE['username'];
	}
	if(isset($_POST['password'])) {
		$password=$_POST['password'];
	} elseif(isset($_GET['password'])) {
		$password=$_GET['password'];
	} elseif(isset($_COOKIE['password'])) {
		$password=$_COOKIE['password'];
	}
	if(isset($username) && isset($password)) {
		$db = connectDB();
		$sql=sprintf($securitySQL,$username,$password);
		$res=$db->query($sql);
		checkDBError($res);
		if($res->numRows() != 1) {
			return false;
		} else {
			$row=$res->fetchRow(MDB2_FETCHMODE_ASSOC);
		}
		if(!preg_match("/^\\\$2a\\\$/",$row['password'])) {
			$mSalt=generateSalt('');
			$mCrypt=crypt($row['password'],$mSalt);
			$upSql = "UPDATE users SET password='{$mCrypt}' WHERE username='{$row['username']}'";
			$resUp=$db->query($upSql);
			checkDBError($resUp,$upSql);
			$row['password'] = $mCrypt;
		}
		if(check_password_hash($row['password'],$password,$username)) {
			$_COOKIE['username'] = $username;
			$_COOKIE['password'] = $password;
			$_COOKIE['ownerID']  = $row['userid'];
			$_COOKIE['superadmin'] = $row['superadmin'];
			setcookie('username',  $username,0,PATH,DOMAIN);
			setcookie('password',  $password,0,PATH,DOMAIN);
			setcookie('ownerID',   $row['userid'],  0,PATH,DOMAIN);
			setcookie('superadmin',$row['superadmin'],  0,PATH,DOMAIN);
			return true;
		} else {
			logout();
			return false;
		}
	} else {
		return false;
	}

}

function doLoginBox() {
	print "<!DOCTYPE html>\n";
	print "<html>\n";
	print "<head>\n";
	print "<title>System Login</title>\n";
	print "</head>";
	print "<body>";
	print "<form method=\"POST\" action=\"{$referer}\">\n";
	print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
	print "<tr><td bgcolor=\"#cacaca\">Username</td><td><input type=\"text\" name=\"username\"></td></tr>\n";
	print "<tr><td bgcolor=\"#cacaca\">Password</td><td><input type=\"password\" name=\"password\"></td></tr>\n";
	print "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Login\"></td></tr>\n";
	print "</table>\n";
	print "</form>\n";
	print "</body>\n";
	print "</html>\n";
	exit();
}
?>
