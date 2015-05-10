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
  $error = '';
  $arr_results = array();
  $cat_results = array();
  $mysqli = NULL;
  
  //try get connection
  $mysqli = get_connection();
  if ($mysqli->connect_errno)
    $error .= 'Failed connect MySQL: (' . $mysqli->connect_errno . ')' . $mysqli->connect_error . '<br>';
    
  $sql = "SELECT id,name,category,length,rented from Movies290";
  if ($result = $mysqli->query($sql)) { //got result
    while ($row = $result->fetch_row()) {
      array_push($arr_results, $row);	
    }
    $result->free();
  } else { //no result
    $error .= 'Select all failed: ' . $mysqli->error . '<br/>';
    $mysqli->close();
  } //end no result
  
  $sql = "SELECT DISTINCT category from Movies290 ORDER BY category";
  if ($result = $mysqli->query($sql)) { //got result
    while ($row = $result->fetch_row()) {
      array_push($cat_results, $row);	
    }
    $result->free();
  } else { //no result
    $error .= 'Select distinct category failed: ' . $mysqli->error . '<br/>';
    $mysqli->close();
  } //end no result
   
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

<?php
  if (!empty($error)) {
    echo '<p>', 'Error(s):<br>', $error, '</p>';
    echo '</body></html>';
    exit();
  }
?>
 
<section>
<form name="myfrm" method="POST" action="movies.php">
<fieldset>
<h2>Add Movie to Inventory</h2>
<p><label for="name">Name:</label><input type="text" id="name" name="name" required>
<p><label for="category">Category:</label>
   <input type="text" id="category" name="category" value="">
<p><label for="length">Length:</label>
   <input type="number" id="length" name="length" min="0" max="4000000000" value="0">
   in minutes
<p><button type="submit" name="insert" value="movies">Add Movie</button>
</fieldset> 
</form>
</section><br><br><br>
<?php
  $name = $category = $length = '';
  $movieId = '';
  $movieBool = '';
  $dbAction = '';
  $validParams = $bindWorked = false;
  $filterCat = '';
  
  $stmt = NULL;

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
    } elseif (isset($_POST['filterOnCat'])) {
      $dbAction = 'filter';
      $filterCat = test_input($_POST['filterCat']);
      $validParams = true;
    } else {
      $error .= 'No dbAction requested<br>';
    }
        
    # out of check form  do get connect, prep stmt, stmt bind
    if (empty($error) && $validParams) {
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
      } elseif ($dbAction == "update") {
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
      } elseif ($dbAction == "filter") {
        if (!empty($filterCat) && $filterCat != "All Movies") {
          $stmt = $mysqli->prepare("select id,name,category,length,rented from Movies290 where category = ?"); 
          if (!$stmt) {
            $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            $mysqli->close();
          } else {
            $bindWorked = $stmt->bind_param("s", $filterCat);
          }
        } elseif (empty($filterCat)) {
          $stmt = $mysqli->prepare("select id,name,category,length,rented from Movies290 where category is null");
          if (!$stmt) {
            $error .= "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            $mysqli->close();
          } else {
            $bindWorked = true;
          }
        }
      } 
    } # end connect prep bind
    
    
    if ($stmt && !$bindWorked) {
      $error .= "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      $stmt->close();
      $mysqli->close();
    }
    
    #check if error..no error mean good to execute
    if (empty($error)) { #good to execute
      $execution = false;
      if ($dbAction != "filter" || $filterCat != "All Movies") $execution = $stmt->execute();
      if ($stmt && !$execution) {
        $error .= "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
      } else {
        $result = NULL;
        if ($dbAction == "filter" && $filterCat != "All Movies") {
          $result = $stmt->get_result();
        } else {
          $sql = "SELECT id,name,category,length,rented from Movies290";
          $result = $mysqli->query($sql);
        }
        if ($stmt) $stmt->close();        
        if ($result) { //got result
          unset($arr_results);
          $arr_results = array();
          while ($row = $result->fetch_row()) {
            array_push($arr_results, $row);	
          }
          $result->free();
        } else { //no result
          $error .= 'Select All failed: ' . $mysqli->error . '<br/>';
        } //end no result
      }
      $mysqli->close();
    } // end good to execute
    
  } // end POST
  
  #output errors    
  if ($error != '') {
    echo '<p>', 'Error(s):<br>', $error, '</p>';
    echo '<br><br><br>';
  }
  
  echo '<h4>Inventory</h4>';
  echo '<section>';
  echo '<form method="POST" action="movies.php">';
  echo '<table><tbody>';
  echo '<tr><th>Name<th>Category<th>Length<th>Status<th>Change Status<th>Delete';
  foreach ($arr_results as $v) {
    echo '<tr><td>',$v[1],'<td>',$v[2],'<td>',$v[3],'<td>',$status[$v[4]],'<td>';
    $status_value = ($v[4]) ? 0 : 1; 
    echo '<button type="submit" name="update" value="',$v[0],'-',$status_value,'">Change Status</button>'; 
    echo '<td><button type="submit" name="delete" value="',$v[0],'-',$status_value,'">Delete</button>';
  } // end foreach
  echo '</tbody></table><br>';
  echo '<button type="submit" name="deleteAll" value="movies">Delete All</button>';
  echo '</form></section>';
  
  echo '<br><br><br><br>';
  echo '<section>';
  echo '<form method="POST" action="movies.php">';
  echo '<fieldset><legend><b>Filter On Category</b></legend>';
  echo '<select name="filterCat">';
  echo '<option value="All Movies" selected>All Movies</option>';
  foreach ($cat_results as $v) {
    if (empty($v[0])) echo '<option value="">None/NULL</option>';
    else echo '<option value="',$v[0],'">',$v[0],'</option>';
  } // end foreach
  echo '</select>';
  echo '<button type="submit" name="filterOnCat" value="movie">Filter</button>';
  echo '</fieldset></form></section>';     
?>
</body>
</html>