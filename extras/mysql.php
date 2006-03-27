<?php
	$mysql_host = "your_hostname.com"; // host name
	$mysql_user = "your_username"; // username
	$mysql_pass = "your_password"; // password
	$mysql_database = "your_database"; // database
// connect
	mysql_connect ($mysql_host, $mysql_user, $mysql_pass) or die(mysql_error());
	mysql_select_db ($mysql_database) or die(mysql_error());
?>