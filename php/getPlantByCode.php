<?php
	require_once('orm/User.php');
	require_once('orm/Plant.php');
	
	$email = $_GET["email"];
	$salt = $_GET["salt"];
	$code = $_GET["code"];
	
	$user = User::findBySignInKey($email, $salt);
	if(get_class($user) == "User"){
		$plant = Plant::findByCode($code);
		if(is_null($plant)){
			die("no plant");
		}
		$plantArray = array(
			"color" => $plant->getColor(),
			"siteName" => $plant->getSite()->getName(),
			"species" => $plant->getSpecies(),
			"circle" => $plant->getCircle(),
			"validated" => $plant->getSite()->getValidationStatus($user),
			"observationMethod" => $plant->getSite()->getObservationMethodPreset($user),
		);
		die("true|" . json_encode($plantArray));
	}
	die("false|Your log in dissolved. Maybe you logged in on another device.");
?>