<?php
// Start session
session_start();

/*********************************************
* Andrew Palma 
* CS290   Spring 2015
* 05-Jun-2015
* Final Project v1.1
*
* primary page for the session stuff
*
*********************************************/
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// You need to provide appropriate values for
// HOST, USERNAME, PASSWORD, DBNAME
function get_connection() {
      $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DBNAME);
      return $mysqli;
}

// try set/reset timeout to 30 mins
ini_set('session.gc_maxlifetime', '900'); 

// Establish link to database
$mysqli = get_connection();
if ($mysqli->connect_errno) die ("Can't connect to database: " . $mysqli->connect_error);

// Run a quick check to see if we are an authenticated user or not
// First, we set a 'is the user logged in' flag to false by default. 
$isUserLoggedIn = false;

if (!($stmt = $mysqli->prepare("SELECT * FROM users290 WHERE session_id = ? LIMIT 1"))) {
     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$sid = session_id();
if (!$stmt->bind_param("s", $sid)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}

$userResult = $stmt->get_result(); 
if($userResult->num_rows == 1){
	$_SESSION['user'] = $userResult->fetch_array(MYSQLI_ASSOC);
	if (strcmp($_SESSION['user']['session_id'],session_id()) === 0) $isUserLoggedIn = true;

}
	
if ($userResult) $userResult->free();
if ($stmt) $stmt->close(); 

if(basename($_SERVER['PHP_SELF']) != 'login.php') {
		if (! $isUserLoggedIn) {
		   header('Location: login.php'); 
		   exit;
	}
}
?>