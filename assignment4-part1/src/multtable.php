<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
   
    $foo = $got_min = $got_max = False;
    $error = '';
    $num_row = $num_col = 0;
    
    function missing_str($a) {
        return "Missing parameter " . $a . "<br>";
    }
    
    function min_str($a) {
        return "Minimum " . $a . " larger than maximum<br>";
    }
    
    function int_str($a) {
        return $a . " must be an integer<br>";
    }
    
    function my_is_int($a) {
        return preg_match("/^[0-9]+$/", $a);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        # commence validating stuff
        $foo = True;
      
        if (empty($_GET['min-multiplicand'])) $error .= missing_str("min-multiplicand");
        elseif (my_is_int($_GET['min-multiplicand']) != 1)  $error .= int_str("min-multiplicand");
        else $got_min = True;
      
        if (empty($_GET['max-multiplicand'])) $error .= missing_str("max-multiplicand");
        elseif (my_is_int($_GET['max-multiplicand']) != 1)  $error .= int_str("min-multiplicand");
        else $got_max = True;
      
        if ($got_min && $got_max) {
            $got_min = $got_max = False;
            if ($_GET['min-multiplicand'] > $_GET['max-multiplicand']) $error .= min_str("multiplicand");
            else $num_row = $_GET['max-multiplicand'] - $_GET['min-multiplicand'] +2;
        }
      
        if (empty($_GET['min-multiplier'])) $error .= missing_str("min-multiplier");
        elseif (my_is_int($_GET['min-multiplier']) != 1)  $error .= int_str("min-multiplier");
        else $got_min = True;
      
        if (empty($_GET['max-multiplier'])) $error .= missing_str("max-multiplier");
        elseif (my_is_int($_GET['max-multiplier']) != 1)  $error .= int_str("max-multiplier");
        else $got_max = True;
      
        if ($got_min && $got_max) {
            $got_min = $got_max = False;
            if ($_GET['min-multiplier'] > $_GET['max-multiplier']) $error .= min_str("multiplier");
            else $num_col = $_GET['max-multiplier'] - $_GET['min-multiplier'] + 2;
        }        
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <!-- got css styling for table from:
    	http://www.textfixer.com/tutorials/css-tables.php   -->
      <!-- CSS goes in the document HEAD or added to your external stylesheet -->
    <style type="text/css">
    table.gridtable {
	  font-family: verdana,arial,sans-serif;
	  font-size:11px;
	  color:#333333;
	  border-width: 1px;
	  border-color: #666666;
	  border-collapse: collapse;
    }
    table.gridtable th {
	  border-width: 1px;
	  padding: 8px;
	  border-style: solid;
	  border-color: #666666;
	  background-color: #dedede;
    }
    table.gridtable td {
	  border-width: 1px;
	  padding: 8px;
	  border-style: solid;
	  border-color: #666666;
	  background-color: #ffffff;
    }
    </style>
<!-- see the reference above regarding css style of table -->

    <title>CS290 Assignment 4 - multtable</title>
  </head>
  <body>
<?php
    if (!$foo) {
        echo "You need to use a GET , you used " . $_SERVER['REQUEST_METHOD'] . "<br>";
    } elseif ( !empty($error)) {
        echo $error;    
    } else {   
        $min_across = $_GET['min-multiplier'] - 1;
        $min_down = $_GET['min-multiplicand'] - 1;
        
        echo '<table class="gridtable"><tbody>';
        
        for ($i=0; $i < $num_row; $i++) {
            echo '<tr>';
            for ($j=0; $j < $num_col; $j++) {
                if ($i == 0 && $j == 0) echo '<th>';
                elseif ($i == 0) printf("<th> %d", $min_across + $j);
                elseif ($j == 0) printf("<th> %d", $min_down + $i);
                else printf("<td> %d", ($min_across+$j) * ($min_down+$i));
            }
        }
       
        echo '</tbody></table>'; 
    }  
?>
  </body>
</html>
