<?php


header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrPostData = $data['inviteeData'];

//print_r($arrPostData);


//print_r($arrConferenceData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
	
	//print_r($arrPostData["inviteeData"]);
	
	// Does Invitee already exist on system
	
	$sql = "SELECT inviteeID from invitees WHERE emailAddress='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["emailAddress"])."'";
		
	$rs_invitee = mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
	
	if (mysqli_num_rows($rs_invitee)>0) {
		$rc=mysqli_fetch_assoc ($rs_invitee);
		$inviteeID = $rc["inviteeID"];
	}
		
	$sql = (($inviteeID) ? "UPDATE" : "INSERT INTO")." invitees SET
	
		firstName ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["firstName"])."',
		lastName ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["lastName"])."',
		companyName ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["companyName"])."',
		emailAddress ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["emailAddress"])."',
		contactNumber ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["contactNumber"])."',
		areaCode ='".mysqli_real_escape_string($conn,$arrPostData["inviteeData"]["areaCode"])."',
		dateCreated ='".mysqli_real_escape_string($conn,date("Y-m-d H:i:s"))."'".	
		
		(($inviteeID) ? "WHERE inviteeID = '".mysqli_real_escape_string($conn,$inviteeID)."'"  : "");
		
//	echo $sql;
	
	mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
	
	if (empty($inviteeID)) {
		$inviteeID = mysqli_insert_id ( $conn );
	}
	
	echo $inviteeID;
	
	
	
	// Always inserting Attendee Data from an Invitee (What to do about duplicates?)
	
//	print_r($arrPostData["attendeeData"]);
	
	foreach ($arrPostData["attendeeData"] as $key=>$attendee) {
	
		//echo "Inserting".chr(10);
	
		$sql = "INSERT INTO attendees SET
				
			eventID ='".mysqli_real_escape_string($conn,$attendee["eventID"])."',
			conferenceID ='".mysqli_real_escape_string($conn,$attendee["conferenceID"])."',
			inviteeID ='".mysqli_real_escape_string($conn,$inviteeID)."',
			firstName ='".mysqli_real_escape_string($conn,$attendee["firstName"])."',
			lastName ='".mysqli_real_escape_string($conn,$attendee["lastName"])."',
			emailAddress ='".mysqli_real_escape_string($conn,$attendee["emailAddress"])."',
			contactNumber ='".mysqli_real_escape_string($conn,$attendee["contactNumber"])."',
			areaCode ='".mysqli_real_escape_string($conn,$attendee["areaCode"])."',
			companyName ='".mysqli_real_escape_string($conn,$attendee["companyName"])."',
			priority ='".mysqli_real_escape_string($conn,$attendee["priority"])."',
			dateCreated ='".mysqli_real_escape_string($conn,$attendee["dateCreated"])."',
			firstChoice ='".mysqli_real_escape_string($conn,$attendee["firstChoice"])."',
			secondChoice ='".mysqli_real_escape_string($conn,$attendee["secondChoice"])."',
			thirdChoice ='".mysqli_real_escape_string($conn,$attendee["thirdChoice"])."',
			accepted ='".mysqli_real_escape_string($conn,$attendee["accepted"])."'"; 
							
	
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
	
	}

	//echo "OK";
		
	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>