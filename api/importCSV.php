<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrPostData = $data['attendeeData'];

//print_r($arrConferenceData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
		
	// Always inserting Attendee Data from an Invitee (What to do about duplicates?)
	
	//print_r($arrPostData["attendeeData"]);
	
	foreach ($arrPostData["attendeeData"] as $key=>$attendee) {
	
		$sql = "SELECT 	attendees.attendeeID,attendees.emailAddress,attendees.eventID,attendees.conferenceID,attendees.dateCreated						
				FROM 	attendees 
				WHERE 	attendees.emailAddress ='".mysqli_real_escape_string($conn,$attendee["emailAddress"])."' AND
						attendees.eventID ='".mysqli_real_escape_string($conn,$arrPostData["eventData"]["eventID"])."'";
										  
		$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		$attendeeID=null;
		
		if (mysqli_num_rows($rs)==1) { // attendee already exists
		
			$rc=mysqli_fetch_assoc ($rs);
			
			$attendeeID = $rc["attendeeID"];
					
			$attendee["conferenceID"] = $rc["conferenceID"];
			$attendee["dateCreated"] = $rc["dateCreated"];
			
		} else {
					
			$attendee["conferenceID"] = -1;
			$attendee["dateCreated"] = date("Y-m-d H:i:s");
		}
		
		$attendee["eventID"] = $arrPostData["eventData"]["eventID"];
		
		$sql = (($attendeeID) ? "UPDATE" : "INSERT INTO")." attendees SET
				
			eventID ='".mysqli_real_escape_string($conn,$attendee["eventID"])."',
			conferenceID ='".mysqli_real_escape_string($conn,$attendee["conferenceID"])."',			
			firstName ='".mysqli_real_escape_string($conn,$attendee["firstName"])."',
			lastName ='".mysqli_real_escape_string($conn,$attendee["lastName"])."',
			emailAddress ='".mysqli_real_escape_string($conn,$attendee["emailAddress"])."',
			contactNumber ='".mysqli_real_escape_string($conn,$attendee["contactNumber"])."',
			areaCode ='".mysqli_real_escape_string($conn,$attendee["areaCode"])."',
			companyName ='".mysqli_real_escape_string($conn,$attendee["companyName"])."',
			priority ='".mysqli_real_escape_string($conn,$attendee["priority"])."',
			dateCreated ='".mysqli_real_escape_string($conn,$attendee["dateCreated"])."'".
			
			(($attendeeID) ? "WHERE attendeeID = '".mysqli_real_escape_string($conn,$attendeeID)."'"  : "");
	
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		

	}

	echo "OK";
		
	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>