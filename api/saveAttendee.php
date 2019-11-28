<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrAttendeeData = $data['attendeeData'];
$md5pass=null;

//print_r($arrAttendeeData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
	
	
	if ($arrAttendeeData["attendeeID"]==null) { // insert a new attendee
	
		//echo "Inserting".chr(10);
		
		$arrAttendeeData["conferenceID"] = -1; // force into holding List
	
		$sql = "INSERT INTO attendees SET
				
			eventID ='".mysqli_real_escape_string($conn,$arrAttendeeData["eventID"])."',
			conferenceID ='".mysqli_real_escape_string($conn,$arrAttendeeData["conferenceID"])."',
			inviteeID ='".mysqli_real_escape_string($conn,$arrAttendeeData["inviteeID"])."',
			firstName ='".mysqli_real_escape_string($conn,$arrAttendeeData["firstName"])."',
			lastName ='".mysqli_real_escape_string($conn,$arrAttendeeData["lastName"])."',
			emailAddress ='".mysqli_real_escape_string($conn,$arrAttendeeData["emailAddress"])."',
			contactNumber ='".mysqli_real_escape_string($conn,$arrAttendeeData["contactNumber"])."',
			areaCode ='".mysqli_real_escape_string($conn,$arrAttendeeData["areaCode"])."',
			companyName ='".mysqli_real_escape_string($conn,$arrAttendeeData["companyName"])."',
			priority ='".mysqli_real_escape_string($conn,$arrAttendeeData["priority"])."',
			dateCreated ='".mysqli_real_escape_string($conn,$arrAttendeeData["dateCreated"])."',
			firstChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["firstChoice"])."',
			secondChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["secondChoice"])."',
			thirdChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["thirdChoice"])."',
			allocationStatus ='".mysqli_real_escape_string($conn,$arrAttendeeData["allocationStatus"])."',
			invited ='".mysqli_real_escape_string($conn,$arrAttendeeData["invited"])."',
			accepted ='".mysqli_real_escape_string($conn,$arrAttendeeData["accepted"])."',
			attended ='".mysqli_real_escape_string($conn,$arrAttendeeData["attended"])."'";
							
	
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
	
		echo mysqli_insert_id ( $conn ) ;
		
						
		
	} else { //better update the records
	
		
		$sql = "UPDATE attendees SET
			
			eventID ='".mysqli_real_escape_string($conn,$arrAttendeeData["eventID"])."',
			conferenceID ='".mysqli_real_escape_string($conn,$arrAttendeeData["conferenceID"])."',
			inviteeID ='".mysqli_real_escape_string($conn,$arrAttendeeData["inviteeID"])."',
			firstName ='".mysqli_real_escape_string($conn,$arrAttendeeData["firstName"])."',
			lastName ='".mysqli_real_escape_string($conn,$arrAttendeeData["lastName"])."',
			emailAddress ='".mysqli_real_escape_string($conn,$arrAttendeeData["emailAddress"])."',
			contactNumber ='".mysqli_real_escape_string($conn,$arrAttendeeData["contactNumber"])."',
			areaCode ='".mysqli_real_escape_string($conn,$arrAttendeeData["areaCode"])."',
			companyName ='".mysqli_real_escape_string($conn,$arrAttendeeData["companyName"])."',
			priority ='".mysqli_real_escape_string($conn,$arrAttendeeData["priority"])."',
			dateCreated ='".mysqli_real_escape_string($conn,$arrAttendeeData["dateCreated"])."',
			firstChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["firstChoice"])."',
			secondChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["secondChoice"])."',
			thirdChoice ='".mysqli_real_escape_string($conn,$arrAttendeeData["thirdChoice"])."',
			allocationStatus ='".mysqli_real_escape_string($conn,$arrAttendeeData["allocationStatus"])."',
			invited ='".mysqli_real_escape_string($conn,$arrAttendeeData["invited"])."',
			accepted ='".mysqli_real_escape_string($conn,$arrAttendeeData["accepted"])."',
			attended ='".mysqli_real_escape_string($conn,$arrAttendeeData["attended"])."'

			WHERE attendeeID = '".mysqli_real_escape_string($conn,$arrAttendeeData["attendeeID"])."'"; 

		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);

		echo $arrAttendeeData["attendeeID"];
	}
	
		
	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>