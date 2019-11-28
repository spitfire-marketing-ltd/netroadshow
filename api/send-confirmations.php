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
		
		
	
		$filename = dirname(__FILE__).'/emails/investor-confirmation.html';
		$handle = fopen($filename, "r");
		$TEMPLATE = fread($handle, filesize($filename));


	foreach ($arrInviteList as $entry) {
		
		//print_r($entry);	
		
		$PIN = "";
		
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
		
		if ($rc["PIN"]=="") {
		
			####################################################################
			
			$key = SERVERKEY;
			
			$userKey = makeKey($rc["userName"], SERVERKEY, $rc["userPass"]);	
			
			$e2 = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
			$LBPassword = $e2->decrypt($rc["LBPassword"], $userKey);

			$auth['lindenbaum_user'] = $rc["userName"];
			$auth['lindenbaum_pass'] = $LBPassword;
			
			$lindenbaum_conf_id = $rc["LBConfID"];
			
			####################################################################
			
			// Submit attendee to LB
			
			####################################################################		
			#
			# First things first -
			# Has the conference started
			# if yes, then we have to use a different method
			# if no, then we can use the existing method
			#
			####################################################################
			
			$url_send = $api_url.'conferences/'.substr($lindenbaum_conf_id,5).'/';
				
			$debug = 0;
			
			if ($debug) echo "ENTRY POINT 1: ".$url_send."\n";
		
			
			$result =  getData($url_send, $auth , "POST");
			$arr_result = json_decode($result,1);
			
			if ($debug) {
				
				echo "RESULTS for ".$url_send.":<br/>".$result."<<<<EOF";
				print_r($arr_result);
				
				/*
				{
				  "id" : "...",
				  "reservation-id" : "...",
				  "recording-state" : "ON",
				  "auditorium-state" : "CONFERENCING",
				  "lock-state" : "OPEN",
				  "state" : "ABSENT",
				  "online-collaboration-link" : "..."
				}
				*/
			}
			
			if ($arr_result["state"]!="RUNNING") {
		
				#################################################################################
				#
				# Conference is OPEN so we can use the resevation method to book in a participant
				#
				#################################################################################
				// SET PARTICIPANT DATA
				#################################################################################
				
				
				
				
				$arr_webinar_registration_data = array();
				
				$indx = 0;
				

				$API_data[0] = array(		
							'id' => '',
							'notifications' => array (
								'email-address' => $rc["emailAddress"],
								'sms-address' => '',
								'email-state' => 'INVITED',
								'sms-state' => 'DISABLED'
							),
							'pin' => '',
							'company' => $rc["companyName"],
							'name' => $rc["firstName"].' '.$rc["lastName"],
							'role' => 'PARTICIPANT',
							'function' => '',
							'phone' => $rc["areaCode"].' '.$rc["contactNumber"],
							'phone-alternative' => '',
							'dial-out' => false,
							'online-join-link' => '',
							'contact-id' => '',	
							/*
							'webinar-registration-data' => array (
								'0' => array (
										"name" => "City Location",
										"value" => $rc["City Location"]
									  ),
								'1' => array (
										"name" => "",
										"value" => ""
									  )
							)
							*/

							
				);
				
			
				if ($debug) {
				
					echo "DATA:<br/>";
					print_r($API_data);
				}

				$str_data = json_encode($API_data);

				####################################################################
				// UPLOAD PARTICIPANT DATA
				####################################################################
					
				// print $str_data;
				
				$url_send = $api_url.'reservations/'.$lindenbaum_conf_id.'/participants/';
				
			
				if ($debug) echo $url_send;
		
				
				if ($debug) {
				
					echo "DATA:<br/>";
					print_r($API_data);
				}
				
				$result =  postData($url_send, $str_data , $auth , "POST");
				$arr_result = json_decode($result,1);
				
				if ($debug) {
					
					echo "RESULTS for ".$url_send.":<br/>".$result."<<<<EOF";
					print_r($arr_result);
				}
				$PIN = $arr_result[count($arr_result)-1]["pin"];
				
				########################################################################
				
				
			} else {
				
				###############################################################################################
				#
				# Conference is CLOSED/LOCKED so we have to use the conference method to book in a participant
				#
				###############################################################################################
				// SET PARTICIPANT DATA
				###############################################################################################
				
				if ($debug) {
					echo "Using Conference method\n";
				}
				//print_r($arr_registration_info);
				
				$arr_webinar_registration_data = array();
				
				$indx = 0;
				
				//print_r($arr_webinar_registration_data);

				$API_data = array(	
							  
							  "label" => $rc["firstName"]." ".$rc["lastName"],
							  "role"  => "PARTICIPANT",

				);
				
				//echo "DATA:<br/>";
			//	print_r($API_data);
				if ($debug) {
				
					echo "DATA:<br/>";
					print_r($API_data);
				}

				$str_data = json_encode($API_data);

				####################################################################
				// UPLOAD PARTICIPANT DATA
				####################################################################
					
				// print $str_data;
				
				$url_send = $api_url.'conferences/'.$lindenbaum_conf_id.'/participants/';
				
			
				if ($debug) {
					echo $url_send;
					echo "DATA:<br/>";
					print_r($API_data);
				}
				
				$result =  postData($url_send, $str_data , $auth , "POST");
				$arr_result = json_decode($result,1);
				
				if ($debug) {
					
					echo "RESULTS for ".$url_send.":<br/>".$result."<<<<EOF";
					print_r($arr_result);
					/*
					{
					  "id" : "...",
					  "label" : "...",
					  "role" : "MODERATOR",
					  "audio-state" : "NONE",
					  "caller-phone" : "...",
					  "callee-phone" : "...",
					  "dial-out" : true,
					  "rts-index" : 12345.0,
					  "pin" : "...",
					  "begin-date" : 12345.0,
					  "online-collaboration-state" : "DISCONNECTED",
					  "online-state" : "DISCONNECTED",
					  "rts-state" : "ON"
					}
					*/
				}
				$PIN = $arr_result["pin"];
				
			}
			
			if  ($PIN=="") { 
		
				$error["token"] = "UNABLE TO RETRIEVE PIN";	
				$error["status"] = $result;	
				$error["payload"] = $reservationData;

				$conference["onError"]=true;
				$conference["errorMessage"]=$result;
				
				echo json_encode($error);
			
				#################################################################################
				
				// um.... problem...
				// Send Email notificiation
				
				############ Notification no longer ################
				
				$mail_config = array("subject" => "No PIN allocated: KIPLING", 
							"message" => "<h1>".$group_name."</h1><h2>".$event_title."</h2><h4>".date("l j F Y @ H:i",strtotime($rc_event["event_date"]))." </h4><p>".$fielddata["Forename"]." ".$fielddata["Surname"]."</p><p>".$emailaddr."</p><p>No PIN allocated: ".$PIN."</p><p>KIPLING RESPONSE: ".$result."</p>",
						 
							"sendermail"  => (($from_email) ? $from_email : "service@incommglobal.com"),
							"sendername" => (($from_email) ? $from_email : "Incomm Global Events"),
							"replyaddress" => "service@incommglobal.com",
						 
							"recipient" => "service@incommglobal.com",
							"recipient_name" => "Incomm Global Events Support");
							
				//$response = form2email($mail_config,null,false);
				
				############ Notification no longer ################
				
				$mail_config = array("subject" => "No PIN allocated: KIPLING", 
							"message" => "<h1>".$group_name."</h1><h2>".$event_title."</h2><h4>".date("l j F Y @ H:i",strtotime($rc_event["event_date"]))." </h4><p>".$fielddata["Forename"]." ".$fielddata["Surname"]."</p><p>".$emailaddr."</p><p>No PIN allocated: ".$PIN."</p><p>KIPLING RESPONSE: ".$result."</p>",
						 
							"sendermail"  => (($from_email) ? $from_email : "service@incommglobal.com"),
							"sendername" => (($from_email) ? $from_email : "Incomm Global Events"),
							"replyaddress" => "service@incommglobal.com",
						 
							"recipient" => "sc@spitfiremarketing.co.uk",
							"recipient_name" => "Incomm Global Events Support");
							
				//$response = form2email($mail_config,null,false);
				
				
			
				#################################################################################
			
			} else {
					
			
				####################################################################
				
				$msg["eventID"] = $rc["eventID"];
				$msg["attendeeID"] = $entry["attendeeID"];
			
				$token = JWT::encode($msg, $key, 'HS256');
				
				//echo $token;
				
				$jwt = validate_id_token($token);
				
				//print_r($jwt);
				
				$eventlink = "http://netroadshow.incommglobalevents.com/timeselect/?e=".$token;
				
				
				$sql = "UPDATE attendees SET			
					
					allocationStatus ='".mysqli_real_escape_string($conn,"Confirmation Sent")."' ,
					PIN ='".mysqli_real_escape_string($conn,$PIN)."' 
					
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
				$htmlmessage = str_replace("%PIN%",$PIN,$htmlmessage);	 		
				$htmlmessage = str_replace("%WCA%",$WCA["participant-link"],$htmlmessage);	 		
				
				
				
				
				// echo $htmlmessage;
				
				$result =  $postmark->to($rc["recipientAddress"]);
				$result =  $postmark->from("service@incommglobal.com");
				$result =  $postmark->subject("Event Confirmation");
				$result =  $postmark->plain_message(strip_tags($htmlmessage));
				$result =  $postmark->html_message($htmlmessage);
				$result =  $postmark->send();
			}
		}
	}	

	echo "OK";

	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>