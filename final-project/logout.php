<?php
include_once('config.php');

/*********************************************
* Andrew Palma 
* CS290   Spring 2015
* 05-Jun-2015
* Final Project v1.1
*
* handles logout, the unsetting of session
*
*********************************************/
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');
  
if (!($stmt = $mysqli->prepare("UPDATE users290 SET session_id=NULL WHERE id= ? LIMIT 1"))) {
     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->bind_param("i", $_SESSION['user']['id'])) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
unset($_SESSION['user']);   
$stmt->close();
$mysqli->close();
header('Location: login.php'); 
exit;
?>  