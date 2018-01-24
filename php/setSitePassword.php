<?php
	require_once('orm/User.php');
	require_once('orm/Site.php');
	
	$email = $_GET["email"];
	$salt = $_GET["salt"];
	$siteName = $_GET["siteName"];
	$newPassword = $_GET["newPassword"];
	
	$user = User::findBySignInKey($email, $salt);
	if(get_class($user) == "User"){
		$site = Site::findByName($siteName);
		if(get_class($site) == "Site"){
			if($site->getCreator()->getEmail() == $email){
				if($site->passwordIsCorrect($newPassword)){
					die("false|That is already " . $siteName . "'s password.");
				}
				if($site->setPassword($newPassword)){
					die("true");
				}
				die("false|Password must be at least 8 characters with no spaces.");
			}
			//die("false|You must be the creator of a site to change its password.");
		}
		//die("false|No site with that name exists.");
		die("false|You are not the creator of any site by that name.");
	}
	die("false|Your log in dissolved. Maybe you logged in on another device.");
?>