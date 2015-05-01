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
        

    if(isset($_GET['action']) && $_GET['action'] == 'end') {
	    do_logout();
    }

    if(session_status() == PHP_SESSION_ACTIVE) {
        if (isset($_SESSION['cookie']) && $_SESSION['cookie'] === 'monster') {
            $NO_AUTH = False;
        } elseif  ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['username'])) {
                $_SESSION = array();
                session_destroy();
            } else {           
                $NO_AUTH = False;
	            $_SESSION['username'] = $_POST['username'];
                $_SESSION['cookie'] = 'monster';
            }
        } else do_logout();
                
        if (!$NO_AUTH) {
            if (!isset($_SESSION['visits'])) $_SESSION['visits'] = 0;
            else $_SESSION['visits']++;
        }
	}
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
    <title>CS290 Assignment 4 - content1</title>
  </head>
  <body>
<?php
    echo '<h2>Content1.php</h2>';
    if ($NO_AUTH) {	
	    echo '<p>A username must be entered.<br>';
	    echo 'Click <a href="login.php">here</a> to return to the login screen.<br>';
	
	} else {
	    echo "Hello $_SESSION[username], you have visited this page $_SESSION[visits] times before<br>";
	    echo '<p>Link to <a href="content2.php">content2.php</a><br>';
	    
	    echo '<p><form name="my_form" method="GET">';
	    echo '<input type="hidden" name="action" value="end">';
	    echo 'Click here to <input type="submit" value="Logout">';
	    echo '</form>';
	}
?>
  </body>
</html>
  
  