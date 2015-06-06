<?php
include_once('config.php');

/*********************************************
* Andrew Palma 
* CS290   Spring 2015
* 05-Jun-2015
* Final Project v1.1
*
* workhorse page.  has form for login/registration
* and also does server side stuff
*
*********************************************/
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');


// Reset errors and success messages
$errors = array();
$data = array();
$data['success'] = false;

// Login attempt
if(isset($_POST['loginSubmit']) && $_POST['loginSubmit'] == 'true'){
	//database collation is case-insensitive
	$loginEmail = test_input($_POST['email']);
	$loginPassword 	= test_input($_POST['password']);
	//simple pattern match on email...trusting client validate.js to take care of
	$pattern = '/^\S+@\S+\.\S+$/';
	if (!preg_match($pattern, $loginEmail))
		$errors['loginEmail'] = 'Your email address has invalid format.';
	
	if(strlen($loginPassword) < 8 || strlen($loginPassword) > 20)
		$errors['loginPassword'] = 'Your password must be between 8-20 characters.';
	
	if(!$errors){
		$stmt = $mysqli->prepare("SELECT * FROM users290 WHERE email = ? AND password = ? LIMIT 1");
		$loginEmail = $mysqli->real_escape_string($loginEmail);
		//really insecure using md5, but this is just demo
		$loginPassword = md5($mysqli->real_escape_string($loginPassword));
		$stmt->bind_param("ss", $loginEmail, $loginPassword);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			$user = $result->fetch_array(MYSQLI_ASSOC);
			$result->free();
			$stmt->close();
			$stmt = $mysqli->prepare("UPDATE users290 SET session_id = ? WHERE id = ? LIMIT 1");
			$stmt->bind_param("si", session_id(), $user['id']);
			$stmt->execute();
			$data['success'] = true;
			//this was original way of redirect
			//now do redirect in the ajax .done function
			//header('Location: index.php');
		}else{
			$errors['login'] = 'Invalid email and password combination.';	
		}
		$stmt->close();
		$mysqli->close();
	}
	$data['errors'] = $errors;
    echo json_encode($data);
    exit();	
}

// Register attempt
if(isset($_POST['registerSubmit']) && $_POST['registerSubmit'] == 'true'){
	$registerEmail = test_input($_POST['regEmail']);
	$registerPassword = test_input($_POST['regPassword']);
	$registerConfirmPassword = test_input($_POST['confirmPassword']);
	$pattern = '/^\S+@\S+\.\S+$/';
	if (!preg_match($pattern, $registerEmail)) 
		$errors['registerEmail'] = 'Your email address has invalid format.';
	
	if(strlen($registerPassword) < 8 || strlen($registerPassword) > 20)	
		$errors['registerPassword'] = 'Your password must be between 8-20 characters.';
	
	if($registerPassword != $registerConfirmPassword)
		$errors['registerConfirmPassword'] = 'Your passwords did not match.';
	
	// Check to see if we have a user registered with this email address already
	$stmt = $mysqli->prepare("SELECT * FROM users290 WHERE email = ? LIMIT 1");
	$registerEmail = $mysqli->real_escape_string($registerEmail);
	$stmt->bind_param("s", $registerEmail);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows == 1) 
		$errors['registerEmail'] = 'This email address is already in use.';
	$result->free();
	$stmt->close();
	if(!$errors){
		$registerPassword = md5($mysqli->real_escape_string($registerPassword));
		$dateReg = $mysqli->real_escape_string(date('Y-m-d H:i:s'));
		$stmt = $mysqli->prepare("INSERT INTO users290 SET email = ?, password = ?,
				date_registered = ?");
		$stmt->bind_param("sss", $registerEmail, $registerPassword, $dateReg);
		if($stmt->execute()){
			$data['success'] = true;
			$data['register'] = 'Thank you for registering. You can now log in above.';
		}else{
			$errors['register'] = 'There was a problem registering you. Please check your details and try again.';
		}
		$stmt->close();	
	}
	$mysqli->close();
	$data['errors'] = $errors;
	echo json_encode($data);
    exit();
}
    
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Login to Beer Bucket List</title>
  <link rel="stylesheet" type="text/css" href="default.css"/>

  <script src="jquery-1.11.3.min.js"></script>
  <script src="jquery.validate.min.js"></script>

  	<script type="text/javascript">
       $(document).ready(function() {
     
                 
          $("#loginForm").validate({
             rules : {
                email : { required : true, email : true },
             	password : { required : true, minlength : 8, maxlength : 20 }
             },
             messages : {
                email : {
                   required : "An email is required",
                   email :  "Email address format is name@domain.com"
                },
                password : {
                   required : "A password of 8-20 characters is required",
                   minlength: "At least 8 characters are needed",
                   maxlength: "At most 20 characters is accepted"
                }
             },
             submitHandler : function(form) {
             	
               var postData = $("#loginForm").serialize();   			
    			$('.invalid').remove();
    			$('.fvalid').remove();
    			$(':input', '#registerForm')
    			  .not(':button, :submit, :reset, :hidden')
    			  .val('')
    			  .removeAttr('checked')
    			  .removeAttr('selected');			
    			//alert(postData);
    			$.ajax({ type:'POST', dataType:'json', data:postData })
    			  .done(function(data){
    			     //console.log(data);
    			     //alert(data.success);
    			     if (!data.success) {
    			        if (data.errors.login)
    			           $('#login-group').append('<div class="invalid">' + data.errors.login + '</div>');
    			        if (data.errors.loginEmail)
    			           $('#email-group').append('<div class="invalid">' + data.errors.loginEmail + '</div>');
    			        if (data.errrors.loginPassword)
    			           $('#password-group').append('<div class="invalid">' + data.errors.loginPassword + '</div>');
    			     } else {
    			        //jquery way of redirect
    			        $(location).attr('href', "index.php");
    			        
    			        //js way - that take referring page out of history
    			        //window.location.replace("index.php");
    			        
    			        //other js way
    			        //window.location.href="index.php";
    			     }
    			  })    			     			
             } //submitHandler          
          });  //.validate loginForm
         
          $("#registerForm").validate({
             rules : {
                regEmail : { required:true, email:true},
                regPassword : {required:true, minlength:8, maxlength:20},
                confirmPassword : {required:true, equalTo:"#regPassword"}
             },
             messages : {
                regEmail : {
                   required : "An email is required",
                   email : "Email address format is name@domain.com"
                },
                regPassword : {
                   required : "A password of 8-20 characters is required",
                   minlength: "At least 8 characters are needed",
                   maxlength: "At most 20 characters are accepted"
                },
                confirmPassword: {
                   required : "A password of 8-20 characters is required",
                   equalTo : "Passwords must match"
                }
             },
             submitHandler : function(form) {
                var postData = $("#registerForm").serialize(); 
    			$('.invalid').remove();
    			$('.fvalid').remove();
    			$(':input', '#loginForm')
    			  .not(':button, :submit, :reset, :hidden')
    			  .val('')
    			  .removeAttr('checked')
    			  .removeAttr('selected'); 			
    			//alert(postData);
    			$.ajax({ type:'POST', dataType:'json', data:postData })
    			  .done(function(data){
    			     //console.log(data);
    			     //alert(data.success);
    			     if (!data.success) {
    			        if (data.errors.register)
    			           $('#register-group').append('<div class="invalid">' + data.errors.register + '</div>');
    			        if (data.errors.registerEmail)
    			           $('#regemail-group').append('<div class="invalid">' + data.errors.registerEmail + '</div>');
    			        if (data.errors.registerPassword)
    			           $('#regpassword-group').append('<div class="invalid">' + data.errors.registerPassword + '</div>');
    			        if (data.errors.confirmPassword)
    			           $('#confpassword-group').append('<div class="invalid">' + data.errors.confirmPassword + '</div>');   
    			     } else {	        
    			        $('#register-group').append('<div class="fvalid">' + data.register + '</div>');
    			        $(':input', '#registerForm')
    			          .not(':button, :submit, :reset, :hidden')
    			          .val('')
    			          .removeAttr('checked')
    			          .removeAttr('selected');
    			        $(".fvalid").fadeOut(2000);
    			     }
    			  }) //.done   			
             } //handler
          });
       });
    </script> 
</head>

<body>
  <header><h1>Login / Register Beer Bucket List</h1></header>
	
	<form class="box400" id="loginForm" name="loginForm"  action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div id="login-group">
		<h2 id="login">Login</h2>		
		<!-- error messages here -->
		</div>
		
		<div id="email-group">
		<label for="email">Email Address<span class="info">case insensitive</span></label>
		<input type="text" id="email" name="email" value="<?php echo htmlspecialchars($loginEmail); ?>" />		
		<!-- error messages here -->
		</div>
		
		<div id="password-group">
		<label for="password">Password <span class="info">8-20 chars</span></label>
		<input type="password" id="password" name="password" value="" />
		<!-- error messages here -->
		</div>
		
		<input type="hidden" name="loginSubmit" id="loginSubmit" value="true" />
		<input type="submit" value="Login" />
	</form>
	
	<form class="box400" id="registerForm" name="registerForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div id="register-group">
		<h2 id="register">Register</h2>
		<!-- status messages here -->
		</div>
		
		<div id="regemail-group">
		<label for="regEmail">Email Address<span class="info">case insensitive</span></label>
		<input type="text" id="regEmail" name="regEmail" value="<?php echo htmlspecialchars($registerEmail); ?>" />
		<!-- error messages here -->
		</div>
			
		<div id="regpassword-group">
		<label for="regPassword">Password<span class="info">8-20 chars</span></label>
		<input type="password" name="regPassword" id="regPassword" value="" />
		<!-- error messages here -->
		</div>
			
		<div id="confpassword-group">
		<label for="confirmPassword">Confirm Password<span class="info">Must match Password</span></label>
		<input type="password" id="confirmPassword" value="" name="confirmPassword"/>
		<!-- error messages here -->
		</div>
		
		
		<input type="hidden" id="registerSubmit" name="registerSubmit" value="true" />
		<input type="submit" value="Register" />
	</form>
	
</body>
</html>
