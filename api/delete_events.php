<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrForDeletion = $data['forDeletion'];

//print_r($arrForDeletion);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');

	foreach ($arrForDeletion as $entry) {
		
		//print_r($entry);
		
		
		$sql = "DELETE from events WHERE
				
						 eventID = '".mysqli_real_escape_string($conn,$entry["eventID"])."'"; 
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
	}	

	echo "OK";

	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>