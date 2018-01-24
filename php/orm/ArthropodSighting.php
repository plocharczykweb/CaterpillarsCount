<?php

//require_once('Survey.php');

class ArthropodSighting
{
//PRIVATE VARS
			
	private static $DOMAIN_NAME = "caterpillarscount.unc.edu";
	private static $extraPaths = "";
	
	private static $HOST = "localhost";
	private static $HOST_USERNAME = "username";
	private static $HOST_PASSWORD = "password";
	private static $DATABASE_NAME = "CaterpillarsCount";
	
	private $id;							//INT
	private $survey;
	private $group;
	private $length;
	private $quantity;
	private $photoURL;
	private $notes;
	private $hairy;
	private $rolled;
	private $tented;
	
	private $deleted;

//FACTORY
	public static function create($survey, $group, $length, $quantity, $notes, $hairy, $rolled, $tented) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		if(!$dbconn){
			return "Cannot connect to server.";
		}
		
		$survey = self::validSurvey($dbconn, $survey);
		$group = self::validGroup($dbconn, $group);
		$length = self::validLength($dbconn, $length);
		$quantity = self::validQuantity($dbconn, $quantity);
		$notes = self::validNotes($dbconn, $notes);
		$hairy = filter_var($hairy, FILTER_VALIDATE_BOOLEAN);
		$rolled = filter_var($rolled, FILTER_VALIDATE_BOOLEAN);
		$tented = filter_var($tented, FILTER_VALIDATE_BOOLEAN);
		
		
		$failures = "";
		
		if($group === false){
			$group = "Invalid arthropod group";
			$failures .= "Invalid arthropod group. ";
		}
		if($survey === false){
			$failures .= $group . " is attached to an invalid survey. ";
		}
		if($length === false){
			$failures .= $group . " length must be between 1mm and 300mm. ";
		}
		if($quantity === false){
			$failures .= $group . " quantity must be between 1 and 1000. ";
		}
		if($notes === false){
			$failures .= "Invalid " . $group . " notes. ";
		}
		
		if($failures != ""){
			return $failures;
		}
		
		mysqli_query($dbconn, "INSERT INTO ArthropodSighting (`SurveyFK`, `Group`, `Length`, `Quantity`, `PhotoURL`, `Notes`, `Hairy`, `Rolled`, `Tented`) VALUES ('" . $survey->getID() . "', '$group', '$length', '$quantity', '', '$notes', '$hairy', '$rolled', '$tented')");
		$id = intval(mysqli_insert_id($dbconn));
		mysqli_close($dbconn);
		
		return new ArthropodSighting($id, $survey, $group, $length, $quantity, "", $notes, $hairy, $rolled, $tented);
	}
	private function __construct($id, $survey, $group, $length, $quantity, $photoURL, $notes, $hairy, $rolled, $tented) {
		$this->id = intval($id);
		$this->survey = $survey;
		$this->group = $group;
		$this->length = intval($length);
		$this->quantity = intval($quantity);
		$this->photoURL = $photoURL;
		$this->notes = $notes;
		$this->hairy = filter_var($hairy, FILTER_VALIDATE_BOOLEAN);
		$this->rolled = filter_var($rolled, FILTER_VALIDATE_BOOLEAN);
		$this->tented = filter_var($tented, FILTER_VALIDATE_BOOLEAN);
		
		$this->deleted = false;
	}

//FINDERS
	public static function findByID($id) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$id = mysqli_real_escape_string($dbconn, $id);
		$query = mysqli_query($dbconn, "SELECT * FROM `ArthropodSighting` WHERE `ID`='$id' LIMIT 1");
		mysqli_close($dbconn);
		
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		
		$arthropodSightingRow = mysqli_fetch_assoc($query);
		
		$survey = Survey::findByID($arthropodSightingRow["SurveyFK"]);
		$group = $arthropodSightingRow["Group"];
		$length = $arthropodSightingRow["Length"];
		$quantity = $arthropodSightingRow["Quantity"];
		$photoURL = $arthropodSightingRow["PhotoURL"];
		$notes = $arthropodSightingRow["Notes"];
		$hairy = $arthropodSightingRow["Hairy"];
		$rolled = $arthropodSightingRow["Rolled"];
		$tented = $arthropodSightingRow["Tented"];
		
		return new ArthropodSighting($id, $survey, $group, $length, $quantity, $photoURL, $notes, $hairy, $rolled, $tented);
	}
	
	public static function findArthropodSightingsBySurvey($survey){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `ArthropodSighting` WHERE `SurveyFK`='" . $survey->getID() . "'");
		mysqli_close($dbconn);
		
		$arthropodSightingsArray = array();
		while($arthropodSightingRow = mysqli_fetch_assoc($query)){
			$arthropodSighting = self::findByID($arthropodSightingRow["ID"]);
			array_push($arthropodSightingsArray, $arthropodSighting);
		}
		return $arthropodSightingsArray;
	}

//GETTERS
	public function getID() {
		if($this->deleted){return null;}
		return intval($this->id);
	}
	
	public function getSurvey() {
		if($this->deleted){return null;}
		return $this->survey;
	}
	
	public function getGroup() {
		if($this->deleted){return null;}
		return $this->group;
	}
	
	public function getLength() {
		if($this->deleted){return null;}
		return intval($this->length);
	}
	
	public function getQuantity() {
		if($this->deleted){return null;}
		return intval($this->quantity);
	}
	
	public function getPhotoURL() {
		if($this->deleted){return null;}
		return $this->photoURL;
	}
	
	public function getNotes() {
		if($this->deleted){return null;}
		return $this->notes;
	}
	
	public function getHairy() {
		if($this->deleted){return null;}
		return filter_var($this->hairy, FILTER_VALIDATE_BOOLEAN);
	}
	
	public function getRolled() {
		if($this->deleted){return null;}
		return filter_var($this->rolled, FILTER_VALIDATE_BOOLEAN);
	}
	
	public function getTented() {
		if($this->deleted){return null;}
		return filter_var($this->tented, FILTER_VALIDATE_BOOLEAN);
	}
	
//SETTERS
	public function setPhotoURL($photoURL){
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			$photoURL = self::validPhotoURL($dbconn, $photoURL);
			if($photoURL != false){
				mysqli_query($dbconn, "UPDATE ArthropodSighting SET PhotoURL='$photoURL' WHERE ID='" . $this->id . "'");
				mysqli_close($dbconn);
				$this->photoURL = $photoURL;
				return true;
			}
			mysqli_close($dbconn);
		}
		return false;
	}
	
//REMOVER
	public function permanentDelete()
	{
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			mysqli_query($dbconn, "DELETE FROM `ArthropodSighting` WHERE `ID`='" . $this->id . "'");
			$this->deleted = true;
			mysqli_close($dbconn);
			return true;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//validity ensurance
	public static function validSurvey($dbconn, $survey){
		if(get_class($survey) == "Survey"){
			return $survey;
		}
		return false;
	}
	
	public static function validGroup($dbconn, $group){
		$group = trim($group);
		$groups = array("ant", "aphid", "bee", "beetle", "caterpillar", "daddylonglegs", "fly", "grasshopper", "leafhopper", "moths", "spider", "truebugs", "other", "unidentified");
		if(in_array($group, $groups)){
			return $group;
		}
		return false;
	}
	
	public static function validLength($dbconn, $length){
		$length = intval(preg_replace("/[^0-9]/", "", $length));
		if($length < 1 || $length > 300){
			return false;
		}
		return $length;
	}
	
	public static function validQuantity($dbconn, $quantity){
		$quantity = intval(preg_replace("/[^0-9]/", "", $quantity));
		if($quantity < 1 || $quantity > 1000){
			return false;
		}
		return $quantity;
	}
	
	public static function validPhotoURL($dbconn, $photoURL){
		//TODO: validate domain
		return mysqli_real_escape_string($dbconn, $photoURL);
	}
	
	public static function validNotes($dbconn, $notes){
		return mysqli_real_escape_string($dbconn, $notes);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//FUNCTIONS
	//none
}		
?>