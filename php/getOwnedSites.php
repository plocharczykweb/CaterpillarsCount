<?php
	require_once('orm/User.php');
	require_once('orm/Site.php');
	
	$email = $_GET["email"];
	$salt = $_GET["salt"];
	
	$user = User::findBySignInKey($email, $salt);
	if(get_class($user) == "User"){
		$sites = $user->getSites();
		$sitesArray = array();
		for($i = 0; $i < count($sites); $i++){
			$sitesArray[$i] = array(
				"creator" => $sites[$i]->getCreator()->getEmail(),
				"name" => $sites[$i]->getName(),
				"description" => $sites[$i]->getDescription(),
				"latitude" => $sites[$i]->getLatitude(),
				"longitude" => $sites[$i]->getLongitude(),
				"location" => $sites[$i]->getLocation(),
				"plantCount" => count($sites[$i]->getPlants()),
				"id" => $sites[$i]->getID(),
			);
		}
		die("true|" . json_encode($sitesArray));
	}
	die("false|Your log in dissolved. Maybe you logged in on another device.");
?>