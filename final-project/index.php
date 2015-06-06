<?php  
include_once('config.php');

/*********************************************
* Andrew Palma 
* CS290   Spring 2015
* 05-Jun-2015
* Final Project v1.1
*
* secure landing point
* customized view for a user
*
*********************************************/
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');
// Add a Beer to table
if(isset($_POST['addSubmit']) && $_POST['addSubmit'] == 'true'){
	// Reset errors and success messages
	$errors = array();
	$data = array();
	$data['success'] = false;
	
	$b_name = $mysqli->real_escape_string(test_input($_POST['beer']));
	//database collation is ci/case-insensitive
	$b_name = $b_name;
	$uid = test_input($_POST['user_id']);
	$enjoy = test_input($_POST['likeit']);	
	//see if this person already entered that beer and don't allow dups
	$stmt = $mysqli->prepare("SELECT Beer FROM beers290 WHERE uid = ? AND Beer = ?");
	$stmt->bind_param("is", $uid, $b_name);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows == 1) 
		$errors['beername'] = 'This beer has already been entered.';
	$result->free();
	$stmt->close();
	if(!$errors) {
		$stmt = $mysqli->prepare("INSERT into beers290 (Beer, Enjoy, uid) values
			(?, ?, ?)");
		$stmt->bind_param("sii", $b_name, $enjoy, $uid);
		if (!$stmt->execute()) {
    		$errors['add'] = "Unable to execute stmt for adding record";
		} else {
			$data['id'] = $mysqli->insert_id;
			$data['beer'] = $b_name;
			$data['enjoy'] = $enjoy;
			$data['success'] = true;
			$data['action'] = "Record Added";
		}
	} 
	if ($stmt) $stmt->close();
	$mysqli->close();
	$data['errors'] = $errors;
    echo json_encode($data);
    exit();
}
	
//remove beer from table
if (isset($_POST['action']) && $_POST['action'] == 'remove') {
	// Reset errors and success messages
	$errors = array();
	$data = array();
	$data['success'] = false;
	
	$b_id = test_input($_POST['beer_id']);
	if ($b_id > 0) { //hopefully it's an id and we can go on
				 
		if (!($stmt = $mysqli->prepare("DELETE FROM beers290 WHERE id = ?"))) {
		 	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (!($stmt->bind_param("i", $b_id))) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!($stmt->execute())) {
    		$errors['remove'] = "Unable to delete record: " . $stmt->error;
    		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		} else {
			$data['success'] = true;
			$data['result'] = "Record Deleted";
		}
	} else {
		$errors['remove'] = "Bad id from table. Unable delete"; 
	}
	if ($stmt) $stmt->close();
	$mysqli->close();
	$data['errors'] = $errors;
    echo json_encode($data);
    exit();
}  //end of delete


/****** end of _POST operations  *******/


//make query to create table - start of regular page view
if (!($stmt = $mysqli->prepare("SELECT Beer, Enjoy, id FROM beers290 WHERE uid = ?"))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->bind_param("i", $_SESSION['user']['id'])) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
$myresult = $stmt->get_result(); 
$userBeers = array();
while ($row = $myresult->fetch_array(MYSQLI_ASSOC)) {
   	$userBeers[] = $row;
}
$myresult->free();
$stmt->close();
$mysqli->close();  
?> 

 
<!doctype html>  
<html>  
<head>  
  <meta charset="utf-8"/>  
  <title>Welcome to <?php echo $_SESSION['user']['email']; ?>'s Beer Bucket List</title>  
  <link rel="stylesheet" type="text/css" href="default.css"/> 
  
  <script src="jquery-1.11.3.min.js"></script>
  <script src="jquery.validate.min.js"></script>

  <script type="text/javascript">
    $(document).ready(function() {
     
 //     http://stackoverflow.com/questions/20754465/delete-a-database-row-using-jquery
 		// any time any element with the 'delete_class' on it is clicked, then
		
		
		$( "#myBeerTable" ).on("click", ".delete-class", function(e) {
			$('.invalid').remove();
		//	alert("I got clicked");
 		 	var row = $(this).closest('tr');
  			var data = {
    			action: 'remove',
    			beer_id: row.find('[name="beer_id"]').val()
  			};
		//	alert(data.beer_id);
  			$.post('index.php', data, function(r) {
    	// display messages
        //		alert(r.success);
           		if (!r.success) {
    				if (r.errors.remove)
    			    $('#message').append('<div class="invalid">' + r.errors.remove + '</div>');
    			} else {
    				$('#message').append('<div class="invalid">' + r.result + '</div>');
    				// remove the row, since it is gone from the DB
    				row.remove();
    			}
    			$(".invalid").fadeOut(2000);
			}, 'json');
		});
   
   
          $("#BeerForm").validate({
             rules : {
                likeit : { required : true },
             	beer : { required : true }
             },
             messages : {
                beer : {
                   required : "Drunk already?  Need a name here.",
                },
                likeit : {
                   required : "Simple Yes or No, please",
                }
             },
             // fix placement of error message with radio buttons
             // ref:  http://stackoverflow.com/questions/11123055/jquery-validation-custom-error-message-display-for-radio-button
             errorPlacement: function(error, element) {
            	if (element.attr("type") == "radio") {
                	error.insertBefore(element);
            	} else {
                	error.insertAfter(element);
            	}
        	 },
             submitHandler : function(form) {
             	$('.invalid').remove();
               	var postData = $("#BeerForm").serialize();
               	//alert(postData);
               	
    			$.ajax({ url:'index.php', type:'POST', dataType:'json', data:postData })
    			  .done(function(data){
    			     //console.log(data);
    			     //alert(data.success);
    			     if (!data.success) {
    			        if (data.errors.beername)
    			           $('#beer-group').append('<div class="invalid">' + data.errors.beername + '</div>');
    			        if (data.errors.add)
    			           $('#message').append('<div class="invalid">' + data.errors.add + '</div>');
    			     } else {
    			        $('#message').append('<div class="invalid">' + data.action + '</div>');
    			        // clear text in form data -- i can't figure out the radio????
    			        $('#BeerForm').find("input[type=text]").val('');
    			        //construct new row
    			     	var newRow = $("<tr/>");
    			     	//add td's to the new row
    			     	newRow.append("<td>" + data.beer + "</td>");
    			     	var likes = (data.enjoy == "0")?'No':'Yes';
    			     	newRow.append("<td>" + likes + "</td>");
    			     	var long_td = "<td><input type=\"hidden\" name=\"beer_id\" value=\"";
    			     	long_td += data.id + "\"/><input class=\"delete-class\" type=\"button\"";
    			     	long_td += " name=\"delete_id\" value=\"Delete\" /></td>";
    			     	newRow.append(long_td);
    			     	//add new row to end of table
    			     	//$("#myBeerTable").append(row);
    			     	$('#myBeerTable > tbody:last').append(newRow);
    			     }
    			    $(".invalid").fadeOut(2000);		     
    			  })    			     			
             } //submitHandler          
          });  //.validate BeerForm
         
       });
    </script>  
</head>  
  
<body>  
  <header><h1><?php echo $_SESSION['user']['email']; ?>'s Beer Bucket List</h1></header>  
   <div></div>
   <div></div>
     <table id="myBeerTable" name="myBeerTable">
       <tbody>
         <tr><th>Beer</th><th>Like</th><th></th></tr>
         <?php
            
            foreach ($userBeers as $v) {
               echo '<tr>';
               echo '<td>', $v['Beer'], '</td>';
               $like = ($v['Enjoy'])?'Yes':'No';
               echo '<td>', $like, '</td>';
               echo '<td><input type="hidden" name="beer_id" value="';
               echo $v['id'], '"/><input class="delete-class" type="button"';
               echo ' name="delete_id" value="Delete" /></td>';
               echo '</tr>', "\n";
      	 	} // end foreach 
      	 ?>
       </tbody>
     </table>
   <div></div>
   <div></div>
   <br>
   <p></p>
   <div id="message"> <!-- general messages here -->
   </div>
   <p></p>
   <br><br><br>
      <form class="box400" id="BeerForm" name="BeerForm"  action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div id="add-group">
		<h2 id="add">Thirsty?  Add a Beer!</h2>		
		</div>
		
		<div id="beer-group">
		<label for="beer">Name of Beer<span class="info">case insensitive</span></label>
		<input type="text" id="beer" name="beer" value="" ></input>	
		<!-- specific error messages here -->
		</div>
		
		<div id="like-group">
		<label for="likeit">Like?<span class="info"></span></label>
		<input type="radio" id="radio1" name="likeit" value="1">Yes</input><br>
		<input type="radio" id="radio2" name="likeit" value="0">No</input>
		</div>
		
		<label for="addSubmit">&nbsp;</label>
		<input type="hidden" name="addSubmit" id="addSubmit" value="true" />
		<input type="hidden" name="user_id" id="user_id" value="<?php echo $_SESSION['user']['id']; ?>" />
		<input type="submit" value="Add" />
	</form>
     <br><br> 
    <footer>  
        <a href="logout.php">Logout</a>  
    </footer>  
</body>  
</html>  