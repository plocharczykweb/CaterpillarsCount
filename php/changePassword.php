<?php
	require_once('orm/User.php');
	
	$currentPassword = $_GET["currentPassword"];
	$newPassword = $_GET["newPassword"];
	$email = $_GET["email"];
	
	$user = User::findByEmail($email);
	if(get_class($user) == "User"){
		if($user->passwordIsCorrect($currentPassword)){
			if($user->setPassword($newPassword)){
				$newSalt = $user->signIn($newPassword);
				die($newSalt);
			}
			die("false|New password must be at least 8 characters with no spaces.");
		}
		die("false|Current password is incorrect.");
	}
	die("false|Your log in dissolved. Maybe you logged in on another device.");
?>