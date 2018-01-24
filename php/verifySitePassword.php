<?php
	require_once('orm/User.php');
	require_once('orm/Plant.php');
	
	$email = $_GET["email"];
	$salt = $_GET["salt"];
	$code = $_GET["code"];
	$password = $_GET["password"];
	
	$user = User::findBySignInKey($email, $salt);
	if(get_class($user) == "User"){
		$plant = Plant::findByCode($code);
		if(is_null($plant)){
			die("false|Invalid survey location code.");
		}
		if($plant->getSite()->validateUser($user, $password)){
			die("true|");
		}
		die("false|Invalid site password.");
	}
	die("false|Your log in dissolved. Maybe you logged in on another device.");
?>