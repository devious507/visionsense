<?php

if(!isset($_COOKIE['superadmin']) || ($_COOKIE['superadmin'] != 't')) {
	header("Location: http://my.rtmscloud.com/");
}
?>
