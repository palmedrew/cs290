<?php   #movies.php
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 'On');

  function get_connection() {
    include_once('db_connection.php');
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    return $mysqli;
  }
   
  # http://www.w3schools.com/php/php_form_validation.asp
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  
  $status = array( 0 => "Available", 1 => "Checked Out", "0" => "Available", "1" => "Checked Out");
   
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>CS290 Assignment 4 pt 2</title>
<link rel="stylesheet" href="default.css">
</head>
<body>
<header>
      <h1>CS290 Assignment 4 pt 2</h1>
      <h3>by Andrew Palma</h3>
</header>

<section>
<form name="myfrm" method="POST" action="movies.php">
<fieldset>
<h2>Add Movie to Inventory</h2>
<p><label for="name">Name:</label><input type="text" id="name" name="name" required>
<p><label for="category">Category:</label>
   <input type="text" id="category" name="category" value="">
<p><label for="length">Running Time:</label>
   <input type="number" id="length" name="length" min="0" max="4000000000" value="0">
   in minutes
<p><button type="submit" name="insert" value="movies">Add Movie</button>
</fieldset> 
</form>
</section><br><br><br>
<div id="placeholder">
<?php
  $error = $name = $category = $length = '';
  $movieId = '';
  $movieBool = '';
  $dbAction = '';
  $validParams = $bindWorked = false;
  $arr_results = array();
  $mysqli = $stmt = NULL;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {  #get stuff from form
    if (isset($_POST['insert'])) {
      $dbAction = 'insert';
      if (empty($_POST['name'])) {
        $error .= 'Title is required<br>';
      }  else {
        $name = test_input($_POST['name']);
        if (isset($_POST['category'])) $category = (!empty(test_input($_POST['category']))) ? test_input($_POST['category']) : NULL;
        $length = (int)test_input($_POST['length']);
        $validParams = true;
      } // end if-else name
    } elseif (isset($_POST['update'])) {
      $dbAction = 'update';
      $arr = explode("-", test_input($_POST['update']));
      $movieId = (int)$arr[0];
      $movieBool = (int)$arr[1];
      $validParams = true;
    } elseif (isset($_POST['delete'])) {
      $dbAction = 'delete';
      $arr = explode("-", test_input($_POST['delete']));
      $movieId = (int)$arr[0];
      $validParams = true;
    } elseif (isset($_POST['deleteAll'])) {
      $dbAction = 'deleteAll';
      $validParams = true;
    } else {
      $error .= 'No dbAction requested<br>';
    }
        
    # out of check form  do get connect, prep stmt, stmt bind
    if (empty($error) && $validParams) {   //spota
        $mysqli = get_connection();
        if ($mysqli->connect_errno) {    //spotb
          $error .= 'Failed connect MySQL: (' . $mysqli->connect_errno . ')' . $mysqli->connect_error . '<br>';
        } else { //good connect
          $bindWorked = false;
          $stmt = false;
          if ($dbAction == "insert") {
            $stmt = $mysqli->prepare("insert into Movies290 (name, category, length) values (?, ?, ?)");
            if (!$stmt) {
              $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
              $mysqli->close();
            } else {
              $name = $mysqli->real_escape_string($name);
              if (!empty($category)) $category = $mysqli->real_escape_string($category);
              $bindWorked = $stmt->bind_param("ssi", $name, $category, $length);
            }
          }  elseif ($dbAction == "update") {
            $stmt = $mysqli->prepare("update Movies290 set rented = ? where id = ?"); 
            if (!$stmt) {
              $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
              $mysqli->close();
            } else {
              $bindWorked = $stmt->bind_param("ii", $movieBool, $movieId);
            }
          } elseif ($dbAction == "delete") {
            $stmt = $mysqli->prepare("delete from Movies290 where id = ?"); 
            if (!$stmt) {
              $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
              $mysqli->close();
            } else {
              $bindWorked = $stmt->bind_param("i", $movieId);
            }
          } elseif ($dbAction == "deleteAll") {
            $stmt = $mysqli->prepare("truncate Movies290"); 
            if (!$stmt) {
              $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
              $mysqli->close();
            } else {
              $bindWorked = true;
            }
          } 
        } //end if-else spotb  this is end else of good connect 
    } // end if spota
    # end connect prep bind
    
    if ($stmt && !$bindWorked) {
      $error .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      $stmt->close();
      $mysqli->close();
    }
    
    #check if error..no error mean good to execute
    if (empty($error)) { #good to execute
      if (!$stmt->execute()) {
        $error .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
      } else { //else exe
        $stmt->close();
        $sql = "SELECT id,name,category,length,rented from Movies290";
        if ($result = $mysqli->query($sql)) { //got result
          while ($row = $result->fetch_row()) {
            array_push($arr_results, $row);	
          }
          $result->free();
        } else { //no result
          $error .= 'Select failed: ' . $mysqli->error . '<br/>';
        } //end no result
      } // end els exe 
      $mysqli->close();
    } // end good to execute
    
    
    # check if error and process 
    if ($error != '') echo '<p>', 'Error(s):<br>', $error, '</p>';
    elseif (!empty($arr_results)) {
      echo '<section>';
      echo '<form method="POST" action="movies.php">';
      echo '<table><tbody>';
      echo '<tr><th>Title<th>Category<th>Running Time<th>Status<th>Change Status<th>Delete';
      foreach ($arr_results as $v) {
        echo '<tr><td>',$v[1],'<td>',$v[2],'<td>',$v[3],'<td>',$status[$v[4]],'<td>';
        $status_value = ($v[4]) ? 0 : 1; 
        echo '<button type="submit" name="update" value="',$v[0],'-',$status_value,'">ChangeStatus</button>'; 
        echo '<td><button type="submit" name="delete" value="',$v[0],'-',$status_value,'">Delete</button>';
      } // end foreach
      echo '</tbody></table><br>';
      echo '<button type="submit" name="deleteAll" value="movies">DeleteAll</button>';
      echo '</form></section>';
    }
    
    
  } // end POST
   
?>
</div>
</body>
</html>