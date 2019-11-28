<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

define("JWT_KEY", "nrs-1234");

date_default_timezone_set("Europe/London") ;

$api_url = 'https://www.incomm.online/conference/rest3/';

require_once("JWT.php");

require(dirname(__FILE__).'/postmark.php');

$postmark = new Postmark("d7a37e56-a21b-4282-a067-027a7750e95b","service@incommglobalevents.com");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrInviteList = $data['inviteList'];
//$token = $data['token'];

//print_r($arrForDeletion);





if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
	include (dirname(__FILE__).'/common.php');
	
		//$arrUserData = validate_id_token($token);
	
		//print_r($arrUserData);
	
		// First need to get User Info
		/*
		$sql = "SELECT 	users.*
						
				FROM 	users 
				WHERE 	userName ='".mysqli_real_escape_string($conn,$arrUserData->user->email)."' AND
						userPass ='".mysqli_real_escape_string($conn,md5($arrUserData->user->password))."'";
						
		
		$rs_user=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		$rc_user=mysqli_fetch_assoc ($rs_user);
		*/
		
		$key = SERVERKEY;
	
		$filename = dirname(__FILE__).'/emails/investor-confirmation.html';
		$handle = fopen($filename, "r");
		$TEMPLATE = fread($handle, filesize($filename));


	foreach ($arrInviteList as $entry) {
		
		//print_r($entry);	
		
		$htmlmessage = $TEMPLATE;
		
		$sql = "SELECT attendees.*, attendees.emailAddress as recipientAddress, `events`.*, users.userID, users.LBPassword, CONCAT(users.firstName, ' ' , users.lastName) as userFullName, users.userName, users.userPass, conferences.*, CONCAT(conferences.startTime, ' - ' , conferences.endTime) as conferenceTime,  conferences.LBConfID, companies.*, companies.companyName as groupName, companies.primaryColor as companyPC, companies.secondaryColor as companySC
				FROM attendees 
				JOIN events ON attendees.eventID = events.eventID
				JOIN users ON events.userID = users.userID
				JOIN companies ON companies.companyID = users.clientID
				JOIN conferences ON conferences.conferenceID = attendees.conferenceID
				WHERE attendees.attendeeID =  '".mysqli_real_escape_string($conn,$entry["attendeeID"])."'"; 

		$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		$rc = mysqli_fetch_assoc ($rs);
		
		if ($rc["allocationStatus"]=="Confirmation sent") {
		
		
			####################################################################
			
			$msg["eventID"] = $rc["eventID"];
			$msg["attendeeID"] = $entry["attendeeID"];
		
			$token = JWT::encode($msg, $key, 'HS256');
			
			//echo $token;
			
			$jwt = validate_id_token($token);
			
			//print_r($jwt);
			
			$eventlink = "http://netroadshow.incommglobalevents.com/timeselect/?e=".$token;
			
			
			$sql = "UPDATE attendees SET			
				
				allocationStatus ='".mysqli_real_escape_string($conn,"Confirmation Sent")."' 
				
				WHERE attendeeID = '".mysqli_real_escape_string($conn,$entry["attendeeID"])."'"; 

			mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
			
			$eventdate = DateTime::createFromFormat('Y-m-d', $rc["eventDate"]);		
			
			$eventLogo = (($rc["logoOverride"])? $rc["logoOverride"]: $rc["logo"]);
			$eventPC = (($rc["secondaryColor"])? $rc["secondaryColor"]: $rc["companyPC"]);
			$eventSC = (($rc["primaryColor"])? $rc["primaryColor"]: $rc["companySC"]);
			
			$WCA = json_decode($rc["WCA"],1);
			
			// Notify User			
			
			$htmlmessage = str_replace("%EVENTLOGO%",$eventLogo,$htmlmessage);					
			$htmlmessage = str_replace("%EVENTTITLE%",$rc["eventTitle"],$htmlmessage);	
			$htmlmessage = str_replace("%EVENTDATE%",$eventdate->format('jS F Y'),$htmlmessage);
			$htmlmessage = str_replace("%SPEAKERS%",implode(",",json_decode($rc["speakers"],1)),$htmlmessage);	
			
			$htmlmessage = str_replace("%FIRSTNAME%",$rc["firstName"],$htmlmessage);			
			$htmlmessage = str_replace("%USERFULLNAME%",$rc["userFullName"],$htmlmessage);			
			
			$htmlmessage = str_replace("%TIMELINK%",$eventlink,$htmlmessage);			
			$htmlmessage = str_replace("%PRIMARYCOLOR%",substr($eventPC, 0, -2),$htmlmessage);	 		
			$htmlmessage = str_replace("%SECONDARYCOLOR%",substr($eventSC, 0, -2),$htmlmessage);	 		
			
			$htmlmessage = str_replace("%CONFERENCETIME%",$rc["conferenceTime"],$htmlmessage);			
			$htmlmessage = str_replace("%CAC%",$rc["PAC"],$htmlmessage);	
			$htmlmessage = str_replace("%PIN%",$rc["PIN"],$htmlmessage);	 		
			$htmlmessage = str_replace("%WCA%",$WCA["participant-link"],$htmlmessage);	 		
			
			// echo $htmlmessage;
			
			//echo $rc["recipientAddress"];
			
			$result =  $postmark->to($rc["recipientAddress"]);
			$result =  $postmark->from("service@incommglobal.com");
			$result =  $postmark->subject("Update to an event you are attending");
			$result =  $postmark->plain_message(strip_tags($htmlmessage));
			$result =  $postmark->html_message($htmlmessage);
			$result =  $postmark->send();
		}
	}	

	echo "OK";

	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>