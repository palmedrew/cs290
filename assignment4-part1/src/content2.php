<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
       
    $NO_AUTH = True;

# this logout code taken from lecture
    function do_logout() {
        $_SESSION = array();
	    session_destroy();
	    $filePath = explode('/', $_SERVER['PHP_SELF'], -1);
	    $filePath = implode('/',$filePath);
	    $redirect = "http://" . $_SERVER['HTTP_HOST'] . $filePath;
	    header("Location: {$redirect}/login.php", true);
	    die();
    }
        

    if(session_status() != PHP_SESSION_ACTIVE) do_logout();
    if (!isset($_SESSION['cookie']) || $_SESSION['cookie'] != 'monster') do_logout();
    
	
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8">
    <style type="text/css">
      body {
        text-align: center;
      }
    </style>
    <title>CS290 Assignment 4 - content2</title>
  </head>
  <body>
<?php

    echo '<h2>Content2.php</h2>';
	echo '<p>Link to <a href="content1.php">content1.php</a><br>';

?>
  </body>
</html>
  
  