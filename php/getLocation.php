<?php
	$lat = $_GET["lat"];
	$lng = $_GET["lng"];
	
	$KEY = "AIzaSyC66haLntB413i6pkgSCXl3wpbrS4SPEx4";
	$arr = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $lng . "&key=" . $KEY), true);
	$country = "";
	$region = "";
	
	$addressComponents = $arr["results"][0]["address_components"];
	for($i = 0; $i < count($addressComponents); $i++){
		if(in_array("country", $addressComponents[$i]["types"])){
			$country = $addressComponents[$i]["short_name"];
		}
		else if(in_array("administrative_area_level_1", $addressComponents[$i]["types"])){
			$region = $addressComponents[$i]["short_name"];
		}
	}
	
	if($country == "US" || $country == "CA"){
		die($region);
	}
	die($country);
?>