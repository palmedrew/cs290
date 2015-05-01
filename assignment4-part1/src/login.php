<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
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
  </head>
  <body>
    <h2>Login.php</h2>
    <!-- got this form snippet from 
    	http://diveintohtml5.info/examples/input-placeholder.html  -->
    <form method="POST" action="content1.php" >
      <input type="text" name="username" placeholder="Your username" autofocus>
      <input type="submit" value="Login">
    </form>
  </body>
</html>