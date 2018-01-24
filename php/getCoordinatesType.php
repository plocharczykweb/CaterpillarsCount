<?php
	$latitude = $_GET["latitude"];
	$longitude = $_GET["longitude"];
	$zoom = $_GET["zoom"];
	
	$imgurl = "http://maps.googleapis.com/maps/api/staticmap?center=" . $latitude . "," . $longitude . "&zoom=" . $zoom . "&size=99x99&maptype=roadmap&sensor=false&style=element:labels|visibility:off&style=element:geometry.stroke|visibility:off&style=feature:landscape|element:geometry|saturation:-100&style=feature:water|saturation:-100|invert_lightness:true";
	$pathname = "../images/map/lastCall.png";
	copy($imgurl, $pathname);
	/*
	for($i = 0; $i < 100; $i++){
		echo "<div>";
		for($j = 0; $j < 100; $j++){
			$rgb = imagecolorat ( imagecreatefrompng($pathname) , $j, $i);
			//echo $rgb . "<br/>";
			$r = ($rgb / pow(2, 16)) & 0xFF;
			$g = ($rgb / pow(2, 8)) & 0xFF;
			$b = $rgb & 0xFF;
			//$hex = sprintf("#%02x%02x%02x", $r, $g, $b);
			$hex = "rgba($r, $g, $b, 1)";
			echo "<div style='width:1px;height:1px;display:inline-block;background:" . $hex . ";'></div>";
		}
		echo "</div>";
	}
	
	
	echo "PATH: " . $pathname . "<br/>";
	echo "WIDTH: " . imagesx(imagecreatefrompng($pathname)) . "<br/>";
	
	$rgb = imagecolorat ( imagecreatefrompng($pathname) , 50, 50);
	$r = ($rgb / pow(2, 16)) & 0xFF;
	$g = ($rgb / pow(2, 8)) & 0xFF;
	$b = $rgb & 0xFF;
	//$hex = sprintf("#%02x%02x%02x", $r, $g, $b);
	$hex = "rgba($r, $g, $b, 1)";
	
	echo "COLOR INT: " . $rgb . "...<br/>";
	echo "RGB: " . $hex . "<br/>";
	*/
	if(imagecolorat(imagecreatefrompng($pathname) , 50, 50) <= 7){
		die("water");
	}
	else{
		die("land");
	}
?>