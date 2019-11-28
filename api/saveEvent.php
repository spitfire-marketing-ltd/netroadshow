<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

//require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrEventData = $data['eventData'];
$md5pass=null;

//print_r($arrEventData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');

	$arrEventData = $data['eventData'];

	

	if ($arrEventData["eventID"]==null) {
		//echo "INSERT".chr(10);
				
		//print_r($arrEventData);
		$arrEventData["eventRef"] = date("U");
				
		$sql = "INSERT INTO events SET
				
						eventTitle ='".mysqli_real_escape_string($conn,$arrEventData["eventTitle"])."',
						eventDate ='".mysqli_real_escape_string($conn,$arrEventData["eventDate"])."',
						eventRef ='".mysqli_real_escape_string($conn,$arrEventData["eventRef"])."',
						eventDateUK ='".mysqli_real_escape_string($conn,$arrEventData["eventDateUK"])."',
						eventImage ='".mysqli_real_escape_string($conn,$arrEventData["eventImage"])."',
						userID ='".mysqli_real_escape_string($conn,$arrEventData["userID"])."',
						companyID ='".mysqli_real_escape_string($conn,$arrEventData["companyID"])."',
						speakers ='".mysqli_real_escape_string($conn,json_encode($arrEventData["speakers"]))."',
						logoOverride ='".mysqli_real_escape_string($conn,$arrEventData["logoOverride"])."',
						primaryColor ='".mysqli_real_escape_string($conn,$arrEventData["primaryColor"])."',						
						useCompanyBranding ='".mysqli_real_escape_string($conn,$arrEventData["useCompanyBranding"])."',		
						secondaryColor ='".mysqli_real_escape_string($conn,$arrEventData["secondaryColor"])."'"; 
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		echo mysqli_insert_id ( $conn ) ;
		
	} else { 
		//echo "UPDATE".chr(10);
		if ($arrEventData["eventRef"]=="")
			$arrEventData["eventRef"] = date("U");
		
		$sql = "UPDATE events SET
				
						eventTitle ='".mysqli_real_escape_string($conn,$arrEventData["eventTitle"])."', 
						eventDate ='".mysqli_real_escape_string($conn,$arrEventData["eventDate"])."',
						eventRef ='".mysqli_real_escape_string($conn,$arrEventData["eventRef"])."',
						eventDateUK ='".mysqli_real_escape_string($conn,$arrEventData["eventDateUK"])."',
						eventImage ='".mysqli_real_escape_string($conn,$arrEventData["eventImage"])."',
						userID ='".mysqli_real_escape_string($conn,$arrEventData["userID"])."',
						companyID ='".mysqli_real_escape_string($conn,$arrEventData["companyID"])."',
						speakers ='".mysqli_real_escape_string($conn,json_encode($arrEventData["speakers"]))."',
						logoOverride ='".mysqli_real_escape_string($conn,$arrEventData["logoOverride"])."',
						primaryColor ='".mysqli_real_escape_string($conn,$arrEventData["primaryColor"])."',						
						useCompanyBranding ='".mysqli_real_escape_string($conn,$arrEventData["useCompanyBranding"])."',		
						secondaryColor ='".mysqli_real_escape_string($conn,$arrEventData["secondaryColor"])."'

				WHERE eventID = '".mysqli_real_escape_string($conn,$arrEventData["eventID"])."'"; 
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		echo $arrEventData["eventID"];
		
	}


	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>