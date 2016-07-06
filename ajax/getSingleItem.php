<?php 
require_once '../includes/db.php'; // The mysql database connection script
if(isset($_GET['itemID'])){
	$itemID = $mysqli->real_escape_string($_GET['itemID']);

	$query="SELECT ID, ITEM, STATUS, CREATED_AT FROM shop WHERE id='$itemID'";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	$arr = array();
	if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$arr[] = $row;	
		}
		echo $json_response = json_encode($arr);
	}
	
else{
	echo $json_response = ('[{"ID":"$$","ITEM":"You have entered an invalid ID","STATUS":"0","CREATED_AT":""}]');

	
	}

}
?>