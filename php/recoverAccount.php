<?php
	require_once("orm/User.php");
	
	$email = $_GET["email"];
	$user = User::findByEmail($email);
	
	if(get_class($user) == "User"){
		if($user->recoverPassword()){
			die("true");
		}
	}
	else if(User::emailIsUnvalidated($email)){//check if email is unverified
		die("Check your email to verify your account before recovering your password. Check spam if needed!");
	}
	die("That email is not attached to a Caterpillars Count account!");
?>