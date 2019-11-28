<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

date_default_timezone_set("Europe/London") ;

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrConferenceData = $data['eventSchedule'];
$md5pass=null;
$api_url = 'https://www.incomm.online/conference/rest3/';

//print_r($arrConferenceData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
	include (dirname(__FILE__).'/common.php');
	
	// First need to get User Info
	$sql = "SELECT 	users.*
						
				FROM 	users 
				WHERE 	userID ='".mysqli_real_escape_string($conn,$arrConferenceData["eventData"]["userID"])."'";
						
		
		$rs_user=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		$rc_user=mysqli_fetch_assoc ($rs_user);
		
		$userKey = makeKey($rc_user["userName"], SERVERKEY, $rc_user["userPass"]);	
		
		$e2 = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$LBPassword = $e2->decrypt($rc_user["LBPassword"], $userKey);

		$auth['lindenbaum_user'] = $rc_user["userName"];
		$auth['lindenbaum_pass'] = $LBPassword;
		
	//	echo "LBPASSWORD:".$LBPassword."<<".chr(10);
	
	// loop through scheduler
	// create UK event Time
	// create conference Records
	// book conference with LB
	// write to the DB (Insert record)
	
	$arrConferences = array();
	
	foreach ($arrConferenceData["eventSchedule"] as $key=>$conference) {
	
	//print_r($conference);
	
//	echo $arrConferenceData["eventData"]["eventDate"].chr(10);
	
//	echo $arrConferenceData["eventData"]["eventDate"]." ".$conference["startTime"].":00".chr(10);
	
		
		
		/*
		echo $ukDateTime.chr(10);		
		echo $LBStartTimeU.chr(10);
		echo $LBEndTimeU.chr(10);
		*/
		
	
		if ($conference["conferenceID"]==null) { // insert a new conference
		
			// OK _ New conference needs to be booked with LB
			
			$ukDateTime = strtotime($arrConferenceData["eventData"]["eventDate"]." ".$conference["startTime"].":00");
			
			$reservationData = createReservationData($arrConferenceData["eventData"], $conference);
			
			//print_r($reservationData);
			
			$str_data = json_encode($reservationData);
			
			

			$result =  postData($api_url.'reservations/',$str_data, $auth , "POST");
			
			if (strstr($result,"conf:")) {
				$conference["LBConfID"] = $result;
					
				// conference booked now we need to get additional details
				$result =  getData($api_url.'reservations/'.$conference["LBConfID"], $auth , "POST");
				$arr_reservation = json_decode($result,1);
				//print_r($arr_reservation);
				
				$conference["HAC"] = $arr_reservation["moderator-access-code"];
				$conference["PAC"] = $arr_reservation["participant-access-code"];		

				
				///reservations/{id}/link/webinar-registration
				$conference["WCA"] = getHostLinks($conference["LBConfID"]);
				
		
				//echo "Inserting".chr(10);
			
				$sql = "INSERT INTO conferences SET
						
					eventID ='".mysqli_real_escape_string($conn,$arrConferenceData["eventData"]["eventID"])."',
					eventTimeUK ='".mysqli_real_escape_string($conn,$ukDateTime)."',
					startTime ='".mysqli_real_escape_string($conn,$conference["startTime"])."',
					endTime ='".mysqli_real_escape_string($conn,$conference["endTime"])."',
					duration ='".mysqli_real_escape_string($conn,$conference["duration"])."',
					timeZone ='".mysqli_real_escape_string($conn,$conference["timeZone"])."',
					conferenceLock ='".mysqli_real_escape_string($conn,$conference["conferenceLock"])."',
					conferenceTitle ='".mysqli_real_escape_string($conn,$conference["conferenceTitle"])."',
					PAC ='".mysqli_real_escape_string($conn,$conference["PAC"])."',
					HAC ='".mysqli_real_escape_string($conn,$conference["HAC"])."',
					WCA ='".mysqli_real_escape_string($conn,$conference["WCA"])."',
					LBConfID ='".mysqli_real_escape_string($conn,$conference["LBConfID"])."',					
					maxParticipants ='".mysqli_real_escape_string($conn,$conference["maxParticipants"])."',
					recordAudio ='".mysqli_real_escape_string($conn,$conference["recordAudio"])."',
					expiryDate ='".mysqli_real_escape_string($conn,$conference["expiryDate"])."'";					
			
				mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
			
				$conference["conferenceID"] = mysqli_insert_id ( $conn ) ;
			
			} else {
					$error["token"] = "UNABLE TO BOOK CONFERENCE";	
					$error["status"] = $result;	
					$error["payload"] = $reservationData;

					$conference["onError"]=true;
					$conference["errorMessage"]=$result;
			}
			
		} else { //better update the records
		
		
			if (!empty($conference["LBConfID"]) && (!empty($conference["requestUpdate"]))) {
			
				$ukDateTime = strtotime($arrConferenceData["eventData"]["eventDate"]." ".$conference["startTime"].":00");
			
				$reservationData = createReservationData($arrConferenceData["eventData"], $conference);
			
				//print_r($reservationData);
				
				$str_data = json_encode($reservationData);
				
				$result =  postData($api_url.'reservations/'.$conference["LBConfID"].'/', $str_data , $auth , "PUT");
				//$result =  postData($api_url.'reservations/1113602/', $str_data , $auth , "PUT");
				
				
				
				//echo $api_url.'reservations/'.$conference["LBConfID"].'/'.chr(10);
				//echo $conference["LBConfID"].chr(10);;
				
				
				if (!strstr($result,"conf:")) {
				
					$error["token"] = "UNABLE TO UPDATE CONFERENCE";	
					$error["status"] = $result;	
					$error["payload"] = $reservationData;

					$conference["onError"]=true;
					$conference["errorMessage"]=$result;
//					echo json_encode($error);
//					exit();
			
				} else {
				
					$conference["WCA"] = getHostLinks($conference["LBConfID"]);
				
					$sql = "UPDATE conferences SET
				
						eventID ='".mysqli_real_escape_string($conn,$conference["eventID"])."',
						eventTimeUK ='".mysqli_real_escape_string($conn,$ukDateTime)."',
						startTime ='".mysqli_real_escape_string($conn,$conference["startTime"])."',
						endTime ='".mysqli_real_escape_string($conn,$conference["endTime"])."',
						duration ='".mysqli_real_escape_string($conn,$conference["duration"])."',
						timeZone ='".mysqli_real_escape_string($conn,$conference["timeZone"])."',
						conferenceLock ='".mysqli_real_escape_string($conn,$conference["conferenceLock"])."',
						conferenceTitle ='".mysqli_real_escape_string($conn,$conference["conferenceTitle"])."',
						PAC ='".mysqli_real_escape_string($conn,$conference["PAC"])."',
						HAC ='".mysqli_real_escape_string($conn,$conference["HAC"])."',
						WCA ='".mysqli_real_escape_string($conn,$conference["WCA"])."',
						LBConfID ='".mysqli_real_escape_string($conn,$conference["LBConfID"])."',
						maxParticipants ='".mysqli_real_escape_string($conn,$conference["maxParticipants"])."',
						recordAudio ='".mysqli_real_escape_string($conn,$conference["recordAudio"])."',
						expiryDate ='".mysqli_real_escape_string($conn,$conference["expiryDate"])."'

						WHERE conferenceID = '".mysqli_real_escape_string($conn,$conference["conferenceID"])."'"; 
			
					mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
				}
				
			}
			
			
	
			
		}
		
		array_push($arrConferences,$conference);
		
	
	}

	echo json_encode($arrConferences);
		
	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

function getHostLinks($LBConfID) {
	global $api_url, $auth;
	$result =  getData($api_url.'reservations/'.$LBConfID.'/link/webinar-registration/',$auth , "GET");
	return $result;
}

function createReservationData($arrEventData, $conferenceData) {

	
		
	$conferenceData["eventID"] = $arrEventData["eventID"];		
	
	$LBStartTime = DateTime::createFromFormat( 'U', strtotime($arrEventData["eventDate"]." ".$conferenceData["startTime"].":00") );
	$LBEndTime = DateTime::createFromFormat( 'U', strtotime($arrEventData["eventDate"]." ".$conferenceData["endTime"].":00") );
	
	$ukDateTime = $LBStartTime->format( 'Y-m-d H:i:s' );
	$LBEndTimeU = date_format($LBEndTime, 'U');
	$LBStartTimeU= date_format($LBStartTime, 'U');
	$LBduration = $LBEndTimeU - $LBStartTimeU;
		
	$lbConfID="";
	
	if (strstr($conferenceData["LBConfID"],"conf:"))
		$lbConfID = $conferenceData["LBConfID"];

	$reservationData = array (
  
			  "id" => (($lbConfID) ? $lbConfID: ""),
			  "language" => "en",
			  "topic" => $arrEventData["eventTitle"]." ".$conferenceData["conferenceTitle"],
			  "size-limit" => 250,
			  "moderator-access-code" => '',
			  "participant-access-code" => '',
			  "activation-code" => '',
			  "cost-center" => '',
			  "recording-mode" => "AUTOMATIC",
			  "schedule" => array (
				"type" => "ONETIME",
				"begin-date" => $LBStartTimeU*1000,
				"duration" => $LBduration,	
				"recurrence" => array(
				  "periodicity" => "ONE_TIME_ONLY",
				  "gap" => '',
				  "allowed-weekdays" => array(),
				  "end-date" => '',
				),	
				"time-frame-settings" => null,
				"phone-activation-settings" => null
			  ),
			  
			  "audio-settings" => array(
				"audio-enabled" => true,
				"audio-announcement-on-entry" => "NONE",
				"audio-announcement-on-exit" => "NONE",
				"rollcall-enabled" => false,
				"audio-join-mode" => "CONFERENCING",
				"audio-close-conference-on-moderator-hangup" => false,
				"audio-participant-labeling" => "NO_LABELING",
				"recording-pin" => "",
				"moderator-post-salutation-action" => "JOIN_TO_CONFERENCE",
				"participant-post-salutation-action" => "JOIN_TO_CONFERENCE",
				"dial-out-settings" => array(
				  "dial-out-strategy" => "",
				  "dial-out-call-retry-count" => '',
				  "dial-out-call-retry-delay" => '',
				  "outgoing-call-number" => "",
				  "dial-out-callee-confirm-required" => '',
				  "self-dial-out-enabled" => ''
				),
				"dial-in-settings" => array(
				  "dial-in-enabled" => true,
				 // "dial-in-waiting-room-enabled" => true,
				  "dial-in-security-code" => "",
				  "dial-in-security-code-mode" => "MATCH",
				  "dial-in-phone-number-suppressed" => true,
				  "dial-in-phone-number-types" => array("NATIONAL_CALLER_PAID", "INTERNATIONAL_CALLER_PAID", "NATIONAL_FREECALL", "INTERNATIONAL_FREECALL", "VOIP"),
				  "grant-access-by-phone-number-allowed" => true,
				  "direct-dial-in-phone-numbers" => array()
				),
				"recording-announcement-enabled" => false,
				
				"audio-file-settings" => array(
				  "moderator-salutation-audioid" => "",
				  "participant-salutation-audioid" => "",
				  "waiting-room-audioid" => ""
				),
				
				"lounge-playback-silent-for-single-moderator" => true,
				"streaming-enabled" => false
			  ),
			  
			  
			  "online-settings" => array(
				"online-presentation-enabled" => true,
				"online-collaboration-enabled" => true,
				"online-participant-list-view-enabled" => false,
				"online-participant-chat-enabled" => false,
				"online-participant-chat-type" => "MODERATOR"
			  ),
			  
			  
			  "webinar-settings" => array(
				"webinar-scope" => "PRIVATE",
				"webinar-subtopic" => "",
				"webinar-description" => "",
				"webinar-speaker" => implode(',',$arrEventData["speakers"]),
				"webinar-registration-deadline" => "",
				"webinar-privacy-statement-url" => ""
			  ),
			  
			  
			  "participant-field-settings" => array(
				"webinar-form-fields" => array( 
					array(
					  "name" => "Name",
					  "mandatory" => false
					), 
					array(
					  "name" => "Company",
					  "mandatory" => false
					) ,
					array(
					  "name" => "Email",
					  "mandatory" => false
					) 
					
				)
			  ),
			    
			  
			  "booking-agent" => "STANDARD_WEB_APPLICATION",
			  "enabled" => true
			);
			
	return ($reservationData);
}

?>