<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
   
    $type = $_SERVER['REQUEST_METHOD'];
    $foo = False;
    $response = array();
   
    if ( $type === 'POST') {
        $foo = True;
        $response['Type'] = $type;
        $response['parameters'] = (empty($_POST)) ? NULL : $_POST;
    } elseif ($type === 'GET') {
        $foo = True;
        $response['Type'] = $type;
        $response['parameters'] = (empty($_GET)) ? NULL : $_GET;
    }
    
    
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>CS290 Assignment 4 - loopback</title>
  </head>
  <body>
<?php
    if ($foo) {
        echo json_encode($response);
    } else {
        echo "You need to use a POST|GET , you used " . $type . "\n";
    }
?>
  </body>
</html>
      
    
