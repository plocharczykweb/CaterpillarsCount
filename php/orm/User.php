<?php

require_once('Site.php');

class User
{
//PRIVATE VARS
			
	private static $DOMAIN_NAME = "caterpillarscount.unc.edu";
	private static $extraPaths = "";
	
	private static $HOST = "localhost";
	private static $HOST_USERNAME = "username";
	private static $HOST_PASSWORD = "password";
	private static $DATABASE_NAME = "CaterpillarsCount";
	
	private $id;							//INT
	private $desiredEmail;					//STRING			email that has been signed up for but not necessarilly verified
	private $email;							//STRING			*@*.*, MUST GET VERIFIED
	private $saltedPasswordHash;			//STRING			salted hash of password
	private $salt;							//STRING
	
	private $deleted;

//FACTORY
	public static function create($email, $password) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		if(!$dbconn){
			return "Cannot connect to server.";
		}
		
		$desiredEmail = self::validEmail($dbconn, $email);
		$password = self::validPassword($dbconn, $password);
		
		$failures = "";
		
		if($desiredEmail === false){
			if(filter_var(filter_var($email, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) === false){
				$failures .= "Invalid email. ";
			}
			else{
				$failures .= "That email is already attached to an account. ";
			}
		}
		if($password === false){
			$failures .= "Password must be at least 8 characters with no spaces. ";
		}
		
		if($failures != ""){
			return $failures;
		}
		
		$salt = mysqli_real_escape_string($dbconn, hash("sha512", rand() . rand() . rand()));
		$saltedPasswordHash = mysqli_real_escape_string($dbconn, hash("sha512", $salt . $password));
		
		mysqli_query($dbconn, "INSERT INTO User (`DesiredEmail`, `Salt`, `SaltedPasswordHash`) VALUES ('$desiredEmail', '$salt', '$saltedPasswordHash')");
		$id = intval(mysqli_insert_id($dbconn));
		mysqli_close($dbconn);
		
		return new User($id, $desiredEmail, "", $salt, $saltedPasswordHash);
	}
	private function __construct($id, $desiredEmail, $email, $salt, $saltedPasswordHash) {
		$this->id = intval($id);
		$this->desiredEmail = $desiredEmail;
		$this->email = $email;
		$this->salt = $salt;
		$this->saltedPasswordHash = $saltedPasswordHash;
		
		$this->deleted = false;
	}	

//FINDERS
	public static function findByID($id) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$id = mysqli_real_escape_string($dbconn, $id);
		$query = mysqli_query($dbconn, "SELECT * FROM `User` WHERE `ID`='$id' LIMIT 1");
		mysqli_close($dbconn);
		
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		
		$userRow = mysqli_fetch_assoc($query);
		
		$desiredEmail = $userRow["DesiredEmail"];
		$email = $userRow["Email"];
		$salt = $userRow["Salt"];
		$saltedPasswordHash = $userRow["SaltedPasswordHash"];
		
		return new User($id, $desiredEmail, $email, $salt, $saltedPasswordHash);
	}
	
	public static function findByEmail($email) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$email = self::validEmailFormat($dbconn, $email);
		if($email === false){
			return null;
		}
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `User` WHERE `Email`='$email' LIMIT 1");
		mysqli_close($dbconn);
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		return self::findByID(intval(mysqli_fetch_assoc($query)["ID"]));
	}
	
	public static function findBySignInKey($email, $salt){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$email = self::validEmailFormat($dbconn, $email);
		if($email === false){
			return null;
		}
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `User` WHERE `Email`='" . $email . "' AND `Salt`='" . $salt . "' LIMIT 1");
		mysqli_close($dbconn);
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		return self::findByID(intval(mysqli_fetch_assoc($query)["ID"]));
	}
	
//SIGNERS
	public function signIn($password){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		if(!$this->deleted && $this->EmailHasBeenVerified()){
			if($this->passwordIsCorrect($password)){
				//sign in
				$salt = mysqli_real_escape_string($dbconn, hash("sha512", rand() . rand() . rand()));
				$saltedPasswordHash = mysqli_real_escape_string($dbconn, hash("sha512", $salt . $password));
				
				
				mysqli_query($dbconn, "UPDATE User SET `Salt`='$salt', `SaltedPasswordHash`='$saltedPasswordHash' WHERE `ID`='" . $this->id . "'");
				mysqli_close($dbconn);
				
				$this->salt = $salt;
				$this->saltedPasswordHash = $saltedPasswordHash;
				return $salt;
			}
			mysqli_close($dbconn);
			return false;
		}
	}
	
//GETTERS
	public function getID() {
		if($this->deleted){return null;}
		return intval($this->id);
	}
	
	public function getDesiredEmail() {
		if($this->deleted){return null;}
		return $this->desiredEmail;
	}
	
	public function getEmail() {
		if($this->deleted){return null;}
		return $this->email;
	}
	
	public function getSites(){
		if($this->deleted){return null;}
		return Site::findSitesByCreator($this);
	}
	
	public function getValidationStatus($site){
		if($this->deleted){return null;}
		if(get_class($site) != "Site"){return false;}
		return $site->getValidationStatus($this);
	}
	
	public function getObservationMethodPreset($site){
		if($this->deleted){return null;}
		if(get_class($site) != "Site"){return false;}
		return $site->getObservationMethodPreset($this);
	}
	
//SETTERS
	public function setEmail($email) {
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			$email = self::validEmail($dbconn, $email);
			if($email != false){
				mysqli_query($dbconn, "UPDATE User SET DesiredEmail='$email' WHERE ID='" . $this->id . "'");
				mysqli_close($dbconn);
				$this->desiredEmail = $email;
				self::sendEmailVerificationCodeToUser($this->id);
				return true;
			}
			mysqli_close($dbconn);
		}
		return false;
	}
	
	public function setPassword($password) {
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			$password = self::validPassword($dbconn, $password);
			if($password != false){
				$saltedPasswordHash = mysqli_real_escape_string($dbconn, hash("sha512", $this->salt . $password));
				mysqli_query($dbconn, "UPDATE User SET SaltedPasswordHash='$saltedPasswordHash' WHERE ID='" . $this->id . "'");
				mysqli_close($dbconn);
				$this->saltedPasswordHash = $saltedPasswordHash;
				return true;
			}
			mysqli_close($dbconn);
		}
		return false;
	}
	
	public function setValidStatus($site, $password){
		if($this->deleted){return null;}
		if(get_class($site) != "Site"){return false;}
		return $site->validateUser($this, $password);
	}
	
	public function setObservationMethodPreset($site, $observationMethod){
		if($this->deleted){return null;}
		if(get_class($site) != "Site"){return false;}
		return $site->setObservationMethodPreset($this, $observationMethod);
	}
	
	
//REMOVER
	public function permanentDelete()
	{
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			mysqli_query($dbconn, "DELETE FROM `User` WHERE `ID`='" . $this->id . "'");
			$this->deleted = true;
			mysqli_close($dbconn);
			return true;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//validity ensurance
	public static function validEmail($dbconn, $email){
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false && mysqli_num_rows(mysqli_query($dbconn, "SELECT `ID` FROM `User` WHERE `Email`='" . $email . "' LIMIT 1")) == 0) {
			return mysqli_real_escape_string($dbconn, $email);
		}
		else {
			return false;
		}
	}
	
	public static function validEmailFormat($dbconn, $email){
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			return mysqli_real_escape_string($dbconn, $email);
		}
		else {
			return false;
		}
	}
	
	public static function validPassword($dbconn, $password){
		$spacelessPassword = mysqli_real_escape_string($dbconn, preg_replace('/ /', '', (string)$password));
		
		if(strlen($password) != strlen($spacelessPassword) || strlen($spacelessPassword) < 8){
			return false;
		}
		return $password;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//FUNCTIONS
	public function createSite($name, $description, $latitude, $longitude, $zoom, $location, $password){
		return Site::create($this, $name, $description, $latitude, $longitude, $zoom, $location, $password);
	}
	
	public static function sendEmailVerificationCodeToUser($usersId){
		$verificationCode = (string)rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
		
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$usersId = mysqli_real_escape_string($dbconn, $usersId);
		$query = mysqli_query($dbconn, "SELECT `DesiredEmail` FROM `User` WHERE `ID`='$usersId' LIMIT 1");
		if(mysqli_num_rows($query) == 0){
			mysqli_close($dbconn);
			return false;
		}
		$usersEmail = mysqli_fetch_assoc($query)["DesiredEmail"];
		mysqli_query($dbconn, "UPDATE User SET `EmailVerificationCode`='$verificationCode' WHERE `ID`='$usersId'");
		
		$confirmationLink = hash("sha512", self::findByID($usersId)->getDesiredEmail() . "jisabfa") . "c" . intval($usersId . $verificationCode) * 7;
		
		mail($usersEmail, "Verify your email for Caterpillars Count!", "Welcome to Caterpillars Count! You need to verify your email before you can use your account. Click the following link to confirm your email address.\n\nVERIFY EMAIL:\n" . self::$DOMAIN_NAME . self::$extraPaths . "/php/verifyemail.php?confirmation=$confirmationLink");
		
		mysqli_close($dbconn);
		return true;
	}
	
	public function verifyEmail($verificationCode){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$verificationCode = mysqli_real_escape_string($dbconn, $verificationCode);
		
		if(self::validEmail($dbconn, $this->desiredEmail) === false){
			return false;
		}
		
		$query = mysqli_query($dbconn, "SELECT `EmailVerificationCode` FROM `User` WHERE `ID`='" . $this->id . "' LIMIT 1");
		if(mysqli_num_rows($query) == 0){
			mysqli_close($dbconn);
			return false;
		}
		$usersEmailVerificationCode = mysqli_fetch_assoc($query)["EmailVerificationCode"];
		if($verificationCode == $usersEmailVerificationCode){
			mysqli_query($dbconn, "UPDATE User SET `Email`=`DesiredEmail` WHERE `ID`='" . $this->id . "'");
			mysqli_query($dbconn, "UPDATE User SET `EmailVerificationCode`='' WHERE `ID`='" . $this->id . "'");
			mysqli_close($dbconn);
			return true;
		}
		mysqli_close($dbconn);
		return false;
	}
	
	public function emailHasBeenVerified() {
		if($this->deleted){return null;}
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$query = mysqli_query($dbconn, "SELECT `Email` FROM `User` WHERE `ID`='" . $this->id . "' LIMIT 1");
		if(mysqli_num_rows($query) == 0){
			mysqli_close($dbconn);
			return false;
		}
		$usersEmail = mysqli_fetch_assoc($query)["Email"];
		return ($usersEmail != "");
	}
	
	public static function emailIsUnvalidated($desiredEmail) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$desiredEmail = self::validEmailFormat($dbconn, $desiredEmail);
		if($desiredEmail === false){
			return false;
		}
		$query = mysqli_query($dbconn, "SELECT `ID` FROM `User` WHERE `DesiredEmail`='$desiredEmail' LIMIT 1");
		mysqli_close($dbconn);
		if(mysqli_num_rows($query) == 0){
			return false;
		}
		return true;
	}
	
	public function passwordIsCorrect($password){
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$testSaltedPasswordHash = mysqli_real_escape_string($dbconn, hash("sha512", $this->salt . $password));
		if($testSaltedPasswordHash == $this->saltedPasswordHash){
			mysqli_close($dbconn);
			return true;
		}
		mysqli_close($dbconn);
		return false;
	}
	
	public function recoverPassword(){
		if($this->email != ""){
			$newPassword = bin2hex(openssl_random_pseudo_bytes(4));
			$this->setPassword($newPassword);
			mail($this->email, "Caterpillars Count! password recovery", "Per your request, we here at Caterpillars Count! have reset the password associated with your email (" . $this->email . ") to: " . $newPassword . "\n\nPlease log in now and reset your password to something memorable. Thank you for using Caterpillars Count!");
			return true;
		}
		return false;
	}
}		
?>