<?php

/**
*	MySQL Database Manipulation Web Application
*/

// Require Header
require("includes/header.php");

// Require database connection file
require("includes/connect.php");

session_start();

?>

<h1>Non-Profit Data Management</h1>

<?php

/********************
*	Select table from database to work with.
********************/

// Store all tabels within the database in an array

$sql_show_tables = "SHOW TABLES;";

if($tables_results = mysqli_query($connection, $sql_show_tables)) {

	if(mysqli_num_rows($tables_results)>0) {

		// Display table names as select options
		echo "<h3>Select Table</h3>
		<form action='' name='table-choice' method='post'>
			<select project='TableOptions' id='TableOptions' name='TableOptions'>
				<option disabled selected>Choose Table</option>";

		while($tables_results_name = mysqli_fetch_array($tables_results, MYSQLI_ASSOC)) {
			echo "<option>" . $tables_results_name["Tables_in_testdb"] . "</option>";
		}

		echo "</select>
		<input type='submit' name='choose_table_submit' id='choose_table_submit' value='Choose Table'>
		</form>";

		mysqli_free_result($tables_results);

	} else {

		echo "<p>No results returned.</p>";
	} 

}

// Save selected table in variable

$chosenTable = "";

if(isset($_POST["TableOptions"])) {

	$chosenTable = $_POST["TableOptions"];

	echo "<p><strong>You are working with " . $chosenTable . ".";

	$_SESSION['chosenTable'] = $chosenTable;
}

?>

<div id="tabs">
  <ul>
    <li><a href="#create-table-tab">Create Tables</a></li>
    <li><a href="#add-data-tab">Add Rows</a></li>
    <li><a href="#delete-data-tab">Delete Tables/Rows</a></li>
    <li><a href="#display-data-tab">Display Data</a></li>
  </ul>
  <div id="create-table-tab">
    <?php

		/********************
		*	Create new table in database.
		********************/

	?>
	<h3>Create new table in database.</h3>
	<form action="index.php" method="post" id="crete_table_form">
		<div class="form-group">
			<label for="new_table_name">New Table Name</label><br>
			<input type="text" id="new_table_name" placeholder="New Table Name" class="form-control" name="new_table_name" max-length="20">
			<input type="submit" name="create_table" class="button" value="Create Table">
		</div>
	</form>

	<!-- Create table in database with the specified name-->

	<?php
	// When button is clicked, create table with the specified table name
	if(isset($_POST["create_table"])) {

		// Set table name variable
		$table_name = $_POST["new_table_name"];

		// Create table with the specified name variable
		$sql_create_table = "CREATE TABLE " . $table_name . "(ID INT (4) NOT NULL PRIMARY KEY AUTO_INCREMENT, firstname VARCHAR(20) NOT NULL, lastname VARCHAR(20) NOT NULL, companyname VARCHAR(30), email VARCHAR(30), donationamount INT(10))";

		// Check for successful table creation
		if(mysqli_query($connection, $sql_create_table)) {
			echo "<script>alert('Table created successfully.');</script>";
		} else {
		echo "<script>alert('ERROR: Table could not be created.');</script>";
		}

	}

	?>

  </div>
  <div id="add-data-tab">
  	<h3>Edit data in existing data in database.</h3>
  	<?php

		/********************
		*	Form to add data to existing table.
		********************/

		// Detect colums in selected table
		$inputsSubmitArray = array();

		$sql_get_columns = "SHOW COLUMNS FROM " . $chosenTable .";";

		if($columns_results = mysqli_query($connection, $sql_get_columns)) {

			if(mysqli_num_rows($columns_results)>0) {

				echo "<form action='' method='post'>
						<div class='form-group'>";

				// Create Arrays to populate within while loop
				$labelsArray = array();
				$inputsArray = array();

				$i = 0;

				$columns_results_name = '';

				while($columns_results_name = mysqli_fetch_array($columns_results, MYSQLI_ASSOC)) {

					// Don't display Primary Key fields
					if($columns_results_name["Key"] == "PRI") {
						$hidePRI = "style='display: none;'";
					} else if($columns_results_name["Key"] == "") {
						$hidePRI = "";
					}

					// Cleaning the result of type to use as type in HTML
					$cleanType = $columns_results_name["Type"];
					$cleanType = preg_replace('/[0-9]+/', '', $cleanType);

					if($cleanType == "int()") {
							$cleanType = "number";
						} else if($cleanType == "char()" || $cleanType == "varchar()") {
							$cleanType = "text";
					}

					// Cleaning the result of type to use as max-length in HTML
					$cleanMaxLen = preg_replace("/[^0-9]/","",$columns_results_name["Type"]);

					// Store results within the Arrays
					$labelsArray = ["<label for='" . $columns_results_name["Field"] . "' " . $hidePRI . ">" . $columns_results_name["Field"] . ":</label><br " . $hidePRI . ">"];

					// Give this array key values
					$inputsArray = ["field_num_ " . $i => "<input " . $hidePRI . " type='" . $cleanType . "' id='id_" . $i . $columns_results_name["Field"] . "' placeholder='" . $columns_results_name["Field"] . "' class='form-control' name='" . $columns_results_name["Field"] . "' max-length='" . $cleanMaxLen . "'><br " . $hidePRI . ">"];

					// Convert Arrays to Strings
					$labelsStringConversion = implode(',', $labelsArray);
					$inputsStringConversion = implode(',', $inputsArray);

					// Print converted Arrays
					echo $labelsStringConversion;
					echo $inputsStringConversion;

					if(!$columns_results_name["Key"] == "PRI") {

						${"field_id_$i"} = $columns_results_name["Field"];

						$_SESSION['field_submit_id_' . $i] = ${"field_id_$i"};

						array_push($inputsSubmitArray, ${"field_id_$i"});

					}

					$i++;
					
				}

				echo "<input type='submit' name='add-rows-submit' class='button' value='Add Row'>";
				echo "</div>
				</form>";

				mysqli_free_result($columns_results);

			} else {

				echo "<p>No results returned.</p>";

			}

		}

			/********************
			*	Adding data to table when form is submitted
			********************/

			$submittedDataArray = array();

			if(isset($_POST["add-rows-submit"])) {

   				foreach ($_SESSION as $key=>$val){
    				array_push($submittedDataArray, "'" . $_POST[$val] . "'");
   				}

   				$submittedDataArrayImploded = implode(", ", $submittedDataArray);
   				$submittedDataExploded = explode(", ", $submittedDataArrayImploded, 2);

   				$submissionValueString = implode(", ", $_SESSION);

   				$removeTableName = explode(", ", $submissionValueString, 2);

   				// Data submission query
   				$sql_submit_new_row = "INSERT INTO " . $_SESSION['chosenTable'] . "(" . $removeTableName[1] . ") VALUES (" . $submittedDataExploded[1] . ")";

   				// Run query
   				if (mysqli_query($connection, $sql_submit_new_row)) {
   					$submission_results = "<script>alert('Data submitted successfully.');</script>";
   					echo $submission_results;
   				} else {
   					$submission_results = "<script>alert('ERROR: Unable to execute: " . $sql_submit_new_row . mysqli_error($connection) . ");</script>";
   					echo $submission_results;
   				}

   				$_SESSION = [];

			}
	?>

  </div>
  <div id="delete-data-tab">
  	<p>Placeholder tab.</p>
  </div>
  <div id="display-data-tab">
    <?php
    	$sql_html_table_head = "SHOW COLUMNS FROM " . $chosenTable . ";";
    	$sql_html_table_body = "SELECT * FROM " . $chosenTable . ";";

    	if($html_table_results = mysqli_query($connection, $sql_html_table_head)) {

    		if(mysqli_num_rows($html_table_results)>0) {
    			echo "<table class='table-responsive table'>
    					<thead>
    						<tr>";

    			$html_results_column_name = '';
    			while($html_results_column_name = mysqli_fetch_array($html_table_results, MYSQLI_ASSOC)) {
    				echo "<th>";
    					echo $html_results_column_name["Field"];
    				echo "</th>";
    			}

    			echo "</tr>
    				</thead>";

    		}
    	}

    	if($html_table_results = mysqli_query($connection, $sql_html_table_body)) {

    		if(mysqli_num_rows($html_table_results) >0) {

    			echo "<tbody>";

    			$html_results_row_info = '';
    			while($html_results_row_info = mysqli_fetch_array($html_table_results, MYSQLI_ASSOC)) {
    				echo "<tr>";

    					foreach($html_results_row_info as $row_content) {
    						echo "<td>" . $row_content . "</td>";
    					}

    				echo "</tr>";
    			}

    			echo "</tbody></table>";

    		}
    	}
    ?>
  </div>
</div>

<?php

// Require Footer
require("includes/footer.php");

?>