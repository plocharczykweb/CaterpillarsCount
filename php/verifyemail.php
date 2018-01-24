<?php
	require_once('orm/User.php');
	$confirmed = "false";
	
	$confirmation = $_GET["confirmation"];
	$hashedEmail = substr($confirmation, 0, strrpos($confirmation, "c"));
	$numericPortion = (intval(str_replace($hashedEmail . "c", "", $confirmation))/7);
	$id = substr((intval(str_replace($hashedEmail . "c", "", $confirmation))/7), 0, strlen((intval(str_replace($hashedEmail . "c", "", $confirmation))/7)) - 4);
	$confirmationCode = substr((intval(str_replace($hashedEmail . "c", "", $confirmation))/7), strlen((intval(str_replace($hashedEmail . "c", "", $confirmation))/7)) - 4, strlen((intval(str_replace($hashedEmail . "c", "", $confirmation))/7)));
	$error = "";
	$user = User::findByID($id);
	if($user != null){
		if($user->emailHasBeenVerified()){//if this user verified their email
			$confirmed = "true";
		}
		else{
			$email = $user->getDesiredEmail();
			if(get_class(User::findByEmail($email)) == "User"){//or if they verified it as another user
				$confirmed = "true";
			}
			else if($hashedEmail == hash("sha512", $email . "jisabfa") && $user->verifyEmail($confirmationCode)){//or if 
				$confirmed = "true";
			}
		}
	}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Verify Email Address | Caterpillars Count!</title>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>
		<style>
			*{
				font-family:"Open Sans", sans-serif;
			}
			
			body{
				background:#fff;
				color:#444;
			}
			.header{
				padding:10px;
				text-align:center;
				position:fixed;
				width:100%;
				top:0px;
				left:0px;
				box-sizing:border-box;
				height:55px;
				background: rgb(0,255,213);
				background: -moz-linear-gradient(-45deg, rgba(0,255,213,1) 0%, rgba(0,255,38,1) 100%);
				background: -webkit-linear-gradient(-45deg, rgba(0,255,213,1) 0%,rgba(0,255,38,1) 100%);
				background: linear-gradient(135deg, rgba(0,255,213,1) 0%,rgba(0,255,38,1) 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00ffd5', endColorstr='#00ff26',GradientType=1 );
				border-bottom:4px solid rgba(0,0,0,.2);
			}
			.main{
				text-align:center;
				margin-top:55px;
				padding:20px;
				box-sizing:border-box;
			}
			.main img{
				width:25%;
				max-width:220px;
			}
			h1{
				font-size:25px;
				color:#fff;
				padding:0px;
				margin:0px;
			}
			p{
				font-size:16px;
			}
			#success, #failure{
				display:none;
			}
		</style>
	</head>
	<body>
		<div id="success">
			<!--<div class="header">
				<h1>Verified</h1>
			</div>-->
			<div class="main">
				<img src="../images/emailVerificationSuccess.png"/>
				<p>Your Caterpillars Count! account's email has been verified!</p>
			</div>
		</div>
		<div id="failure">
			<!--<div class="header">
				<h1>Unverified</h1>
			</div>-->
			<div class="main">
				<img src="../images/emailVerificationFailure.png"/>
				<p>Something went wrong, and your Caterpillars Count! account's email has NOT been verified!</p>
			</div>
		</div>
		<script>
			if(<?php echo $confirmed ?>){
				document.getElementById("success").style.display = "block";
			}
			else{
				document.getElementById("failure").style.display = "block";
			}
		</script>
	</body>
</html>