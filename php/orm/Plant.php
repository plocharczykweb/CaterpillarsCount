<?php

require_once('Site.php');

class Plant
{
//PRIVATE VARS
			
	private static $DOMAIN_NAME = "caterpillarscount.unc.edu";
	private static $extraPaths = "";
	
	private static $HOST = "localhost";
	private static $HOST_USERNAME = "username";
	private static $HOST_PASSWORD = "password";
	private static $DATABASE_NAME = "CaterpillarsCount";
	
	private $id;							//INT
	private $site;							//Site object
	private $position;					//STRING			email that has been signed up for but not necessarilly verified
	private $code;
	
	private $deleted;

//FACTORY
	public static function create($site, $position) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		if(!$dbconn){
			return "Cannot connect to server.";
		}
		
		$site = self::validSite($dbconn, $site);
		$position = self::validPositionFormat($dbconn, $position);
		
		$failures = "";
		
		if($site === false){
			$failures .= "Invalid site. ";
		}
		if($position === false){
			$failures .= "Enter a position. ";
		}
		if($failures == "" && is_null(self::findBySiteAndPosition($site, $position)) === false){
			$failures .= "Enter a unique position for this site. ";
		}
		
		if($failures != ""){
			return $failures;
		}
		
		mysqli_query($dbconn, "INSERT INTO Plant (`SiteFK`, `Position`) VALUES ('" . $site->getID() . "', '$position')");
		$id = intval(mysqli_insert_id($dbconn));
		
		$code = self::IDToCode($id);
		mysqli_query($dbconn, "UPDATE Plant SET `Code`='$code' WHERE `ID`='$id'");
		mysqli_close($dbconn);
		
		return new Plant($id, $site, $position, $code);
	}
	private function __construct($id, $site, $position, $code) {
		$this->id = intval($id);
		$this->site = $site;
		$this->position = $position;
		$this->code = $code;
		
		$this->deleted = false;
	}

//FINDERS
	public static function findByID($id) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$id = mysqli_real_escape_string($dbconn, $id);
		$query = mysqli_query($dbconn, "SELECT * FROM `Plant` WHERE `ID`='$id' LIMIT 1");
		mysqli_close($dbconn);
		
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		
		$plantRow = mysqli_fetch_assoc($query);
		
		$site = Site::findByID($plantRow["SiteFK"]);
		$position = $plantRow["Position"];
		$code = $plantRow["Code"];
		
		return new Plant($id, $site, $position, $code);
	}
	
	public static function findByCode($code) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$code = self::validCode($dbconn, $code);
		if($code === false){
			return null;
		}
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `Plant` WHERE `Code`='$code' LIMIT 1");
		mysqli_close($dbconn);
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		return self::findByID(intval(mysqli_fetch_assoc($query)["ID"]));
	}
	
	public static function findBySiteAndPosition($site, $position) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$site = self::validSite($dbconn, $site);
		$position = self::validPositionFormat($dbconn, $position);
		if($site === false || $position === false){
			return null;
		}
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `Plant` WHERE `SiteFK`='" . $site->getID() . "' AND `Position`='$position' LIMIT 1");
		mysqli_close($dbconn);
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		return self::findByID(intval(mysqli_fetch_assoc($query)["ID"]));
	}
	
	public static function findPlantsBySite($site){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `Plant` WHERE `SiteFK`='" . $site->getID() . "'");
		mysqli_close($dbconn);
		
		$plantsArray = array();
		while($plantRow = mysqli_fetch_assoc($query)){
			$plant = self::findByID($plantRow["ID"]);
			array_push($plantsArray, $plant);
		}
		return $plantsArray;
	}

//GETTERS
	public function getID() {
		if($this->deleted){return null;}
		return intval($this->id);
	}
	
	public function getSite() {
		if($this->deleted){return null;}
		return $this->site;
	}
	
	public function getSpecies() {
		if($this->deleted){return null;}
		return "N/A";
	}
	
	public function getPosition() {
		if($this->deleted){return null;}
		return $this->position;
	}
	
	public function getCircle(){
		if($this->deleted){return null;}
		return preg_replace("/[^0-9]/", "", $this->position);
	}
	
	public function getColor(){
		if($this->deleted){return null;}
		$orientation = preg_replace('/[0-9]+/', '', $this->position);
		if($orientation == "A"){
			return "#ff7575";//red
		}
		else if($orientation == "B"){
			return "#75b3ff";//blue
		}
		else if($orientation == "C"){
			return "#5abd61";//green
		}
		else if($orientation == "D"){
			return "#ffc875";//orange
		}
		else if($orientation == "E"){
			return "#9175ff";//purple
		}
		return false;
	}
	
	public function getCode() {
		if($this->deleted){return null;}
		return $this->code;
	}
	
//SETTERS
	
	
//REMOVER
	public function permanentDelete()
	{
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			mysqli_query($dbconn, "DELETE FROM `Plant` WHERE `ID`='" . $this->id . "'");
			$this->deleted = true;
			mysqli_close($dbconn);
			return true;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//validity ensurance
	public static function validSite($dbconn, $site){
		if(get_class($site) != "Site"){
			return false;
		}
		return $site;
	}
	
	public static function validPositionFormat($dbconn, $position){
		$position = mysqli_real_escape_string($dbconn, $position);
		
		if($position == ""){
			return false;
		}
		return $position;
	}
	
	public static function validCode($dbconn, $code){
		$code = mysqli_real_escape_string($dbconn, str_replace("0", "O", preg_replace('/\s+/', '', strtoupper($code))));
		
		if($code == ""){
			return false;
		}
		return $code;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//FUNCTIONS
	public static function IDToCode($id){
		$chars = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		
		//get the length of the code we will be returning
		$codeLength = 0;
		$previousIterations = 0;
		while(true){
			$nextIterations = pow(count($chars), ++$codeLength);
			if($id <= $previousIterations + $nextIterations){
				break;
			}
			$previousIterations += $nextIterations;
		}
		
		//and, for every character that will be in the code...
		$code = "";
		$index = $id - 1;
		$iterationsFromPreviousSets = 0;
		for($i = 0; $i < $codeLength; $i++){
			//generate the character from the id
			if($i > 0){
				$iterationsFromPreviousSets += pow(count($chars), $i);
			}
			$newChar = $chars[floor(($index - $iterationsFromPreviousSets) / pow(count($chars), $i)) % count($chars)];
			
			//and add it to the code
			$code = $newChar . $code;
		}
		
		//then, return a sanitized version of the full code that is safe to use with a MySQL query
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$code = mysqli_real_escape_string($dbconn, $code);
		mysqli_close($dbconn);
		return $code;
	}
}		
?>