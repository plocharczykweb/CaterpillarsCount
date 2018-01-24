<?php
	require_once("orm/User.php");
	$user = User::findBySignInKey($_GET["email"], $_GET["salt"]);
	if(get_class($user) == "User"){
		die("true");
	}
	die("false");
?>