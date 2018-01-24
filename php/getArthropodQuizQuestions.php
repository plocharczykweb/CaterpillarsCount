<?php
	$HOST = "localhost";
	$HOST_USERNAME = "username";
	$HOST_PASSWORD = "password";
	$DATABASE_NAME = "CaterpillarsCount";
	
	$dbconn = mysqli_connect($HOST, $HOST_USERNAME, $HOST_PASSWORD, $DATABASE_NAME);
	$query = mysqli_query($dbconn, "SELECT * FROM `ArthropodQuizQuestions` ORDER BY RAND() LIMIT 10");
	mysqli_close($dbconn);
		
	$questionsArray = array();
	while($questionRow = mysqli_fetch_assoc($query)){
		$question = array($questionRow["PhotoURL"], $questionRow["Answer"]);
		array_push($questionsArray, $question);
	}
	die(json_encode($questionsArray));
	
?>