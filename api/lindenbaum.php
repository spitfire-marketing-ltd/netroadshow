<?
session_start(); header("Cache-control: private");

error_reporting(E_ERROR | E_PARSE | E_ALL); //E_ERROR | E_WARNING | E_PARSE
ini_set("display_errors", 1);

date_default_timezone_set("UTC") ;

$api_url = 'https://www.incomm.online/conference/rest3/';

//include("db_connect.php");

####################################################################
//  TEST SUITE 
/*

www.incommuk.com/customers/wmt

Username -   Spitfire-Steve
Password -   Supermarine-2208-sc

*/
/*	
	Routines to test API functionality and data requirements
	
	1) Create Customers (Companies)
	2) Create/Edit/Delete Conferences (Resevations)
	3) Create/Edit/Delete Attendees
	4) Conference View
	
####################################################################

/* GET CUSTOMER */

/*

{
  "id" : "...",
  "external-id" : "...",
  "username" : "...",
  "password" : "...",
  "name" : "...",
  "company" : "...",
  "email" : "...",
  "phone" : "..."
}
*/

//$lindenbaum_user = "Spitfire-Steve";
//$lindenbaum_pass = "Supermarine-2208-sc";

$auth = array (
	'lindenbaum_user' => "spitfiremarketing",
	'lindenbaum_pass' => "Supermarine-2208-sc"
);

//$lindenbaum_user = "steve cornes";
//$lindenbaum_pass = "Sp!tf!RE18";




// GET ACCOUNT INFO

echo "GET ACCOUNT INFO"."<br>";

$reservation =  getData($api_url.'accounts/21292/', $auth);
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);


echo "GET RESERVATIONS"."<br>";

$auth['lindenbaum_user']="sc@spitfiremarketing.co.uk";
$auth['lindenbaum_pass']="S@c#346834!";


$reservation =  getData($api_url.'reservations/', $auth);
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);

echo "ADD PARTICIPANT"."<br>";

$data[0] = array(		
			'id' => '',
			  'notifications' => array (
				'email-address' => 'stevecornes@gmail.com',
				'sms-address' => '07399270917',
				'email-state' => 'INVITED',
				'sms-state' => 'DISABLED'
			),
			'pin' => '',
			"company" => "OAC abc",
			"name" => "OAC xyz",
			'role' => 'PARTICIPANT',
			'function' => 'Developer',
			'phone' => '+442380442640',
			'phone-alternative' => '',
			'dial-out' => false,
			'online-join-link' => '',
			'contact-id' => '',
			
			"webinar-registration-data" => array(
				array (
					"name" => "City",
					"value" => "Southampton"
				), 
				array (
					"name" => "Work place",
					"value" => "Home"
				)
			)
		);

$str_data = json_encode($data);

/*
$reservation =  postData($api_url.'reservations/conf:1113528/participants/',$str_data , "json", $auth);
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);
*/
echo "ADD RESERVATION"."<br>";


$date_start = DateTime::createFromFormat('d/m/Y', "14/10/2019")->format('U');
$start_date = strtotime($date_start); 
$end_date = strtotime("+1 months", $start_date);

		
$reservationData = array (
  
  "id" => '',
  "language" => "en",
  "topic" => "OAC Test",
  "size-limit" => 250,
  "moderator-access-code" => '',
  "participant-access-code" => '',
  "activation-code" => '',
  "cost-center" => '',
  "recording-mode" => "NONE",
  "schedule" => array (
    "type" => "ONETIME",
    "begin-date" => $date_start*1000,
    "duration" => 60,	
    "recurrence" => array(
      "periodicity" => "ONE_TIME_ONLY",
      "gap" => 0,
      "allowed-weekdays" => array(),
      "end-date" => $date_start*1000
    ),	
    "time-frame-settings" => null,
    "phone-activation-settings" => null
  ),
  
  "audio-settings" => array(
    "audio-enabled" => true,
    "audio-announcement-on-entry" => "NONE",
    "audio-announcement-on-exit" => "NONE",
    "rollcall-enabled" => true,
    "audio-join-mode" => "CONFERENCING",
    "audio-close-conference-on-moderator-hangup" => true,
    "audio-participant-labeling" => "NO_LABELING",
    "recording-pin" => "",
    "moderator-post-salutation-action" => "JOIN_TO_CONFERENCE",
    "participant-post-salutation-action" => "JOIN_TO_CONFERENCE",
    "dial-out-settings" => array(
      "dial-out-strategy" => "SIMULTANEOUS",
      "dial-out-call-retry-count" => 10,
      "dial-out-call-retry-delay" => 30,
      "outgoing-call-number" => "",
      "dial-out-callee-confirm-required" => true,
      "self-dial-out-enabled" => true
    ),
    "dial-in-settings" => array(
      "dial-in-enabled" => true,
      //"dial-in-waiting-room-enabled" => true,
      "dial-in-security-code" => "",
      "dial-in-security-code-mode" => "NONE",
      "dial-in-phone-number-suppressed" => true,
      "dial-in-phone-number-types" => array("NATIONAL_CALLER_PAID", "NATIONAL_CALLER_PAID" ),
      "grant-access-by-phone-number-allowed" => false,
     // "direct-dial-in-phone-numbers" => array("02380442640","073992709617")
    ),
    "recording-announcement-enabled" => false,
	
    "audio-file-settings" => array(
      "moderator-salutation-audioid" => "",
      "participant-salutation-audioid" => "",
      "waiting-room-audioid" => ""
    ),
	
    "lounge-playback-silent-for-single-moderator" => true,
    "streaming-enabled" => true
  ),
  
  
  "online-settings" => array(
    "online-presentation-enabled" => true,
    "online-collaboration-enabled" => true,
    "online-participant-list-view-enabled" => true,
    "online-participant-chat-enabled" => true,
    "online-participant-chat-type" => "PUBLIC"
  ),
  
  
  "webinar-settings" => array(
    "webinar-scope" => "PRIVATE",
    "webinar-subtopic" => "Sub",
    "webinar-description" => "Description",
    "webinar-speaker" => "John Higgins",
    "webinar-registration-deadline" => 0.0,
    "webinar-privacy-statement-url" => ""
  ),
  
  
  "participant-field-settings" => array(
    "webinar-form-fields" => array( 
		array(
		  "name" => "City",
		  "mandatory" => false
		), 
		array(
		  "name" => "Work place",
		  "mandatory" => false
		) 
	)
  ),
  
  /*
  "participants" => array(
	array(		
			'id' => '',
			  'notifications' => array (
				'email-address' => 'stevecornes@gmail.com',
				'sms-address' => '07399270917',
				'email-state' => 'INVITED',
				'sms-state' => 'DISABLED'
			),
			'pin' => '',
			"company" => "OAC abc",
			"name" => "OAC xyz",
			'role' => 'MODERATOR',
			'function' => 'Developer',
			'phone' => '+442380442640',
			'phone-alternative' => '',
			'dial-out' => false,
			'online-join-link' => '',
			'contact-id' => '',
			
			"webinar-registration-data" => array(
				array (
					"name" => "City",
					"value" => "Southampton"
				), 
				array (
					"name" => "Work place",
					"value" => "Home"
				)
			)
		),
	), 
	*/
  
  "booking-agent" => "STANDARD_WEB_APPLICATION",
  "enabled" => true
);



print_r($reservationData);
$str_data = json_encode($reservationData);
/*
$reservation =  postData($api_url.'reservations/',$str_data , "json", $auth);
echo ">>".$reservation."<<";
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);
*/
/*

$reservation =  getData($api_url.'accounts/21292/');
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);

$arr_reservation["last-name"] = "TEST Cornes";

$data[0] = $arr_reservation;

$json_data = json_encode($data);

echo $json_data;

$result2 =  sendPostData($api_url.'accounts/21292/',$json_data);
$arr_result2 = json_decode($result2,1);

print_r($result2);

$reservation =  getData($api_url.'accounts/21292');
$arr_reservation = json_decode($reservation,1);
print_r($arr_reservation);

*/


####################################################################
/*

$url = 'https://showcase.teleconf.de/conference/rest3/';

####################################################################
// GET RESERVATIONS
####################################################################

$result =  getData($url.'reservations/conf:15058/');
$arr_result = json_decode($result,1);
print_r($arr_result);

####################################################################
// SET PARTICIPANT DATA
####################################################################

$data[0] = array(		
			'id' => '24833',
			  'notifications' => array (
				'email-address' => 'stevecornes@gmail.com',
				'sms-address' => '',
				'email-state' => 'INVITED',
				'sms-state' => 'DISABLED'
			),
			'pin' => '',
			'company' => 'Spitfire',
			'name' => 'A N Other updated again 24833',
			'role' => 'PARTICIPANT',
			'function' => 'Developer',
			'phone' => '+442380442640',
			'phone-alternative' => '',
			'dial-out' => false,
			'online-join-link' => '',
			'contact-id' => '',
);

$str_data = json_encode($data);

####################################################################
// UPLOAD PARTICIPANT DATA
####################################################################

$url_send = $url.'reservations/conf:15079/participants/';
echo 'Adding Participant info: '; cr();cr();
echo 'Endpoint: '.$url_send; cr();cr();

$result2 =  sendPostData($url_send, $str_data);
$arr_result2 = json_decode($result2,1);
print_r($arr_result2);

$PIN = $arr_result2["pin"]

*/
####################################################################

####################################################################

function sendPostData($url, $post, $mode="json"){

	global $lindenbaum_user, $lindenbaum_pass;
 
	$ch = curl_init($url);
  
  
	if ($mode=="json")
		$headers= array('Accept: application/json','Content-Type: application/json'); 
	else
		$headers= array('Accept: application/xml','Content-Type: application/xml'); 
		
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:Inc!2018');  // authentication
	curl_setopt($ch, CURLOPT_USERPWD,	$lindenbaum_user.':'.$lindenbaum_pass);  // authentication
	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}

####################################################################

function postData($url, $post, $mode="json" , $auth){

	$ch = curl_init($url);
  
  
	if ($mode=="json")
		$headers= array('Accept: application/json','Content-Type: application/json'); 
	else
		$headers= array('Accept: application/xml','Content-Type: application/xml'); 
		
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:Inc!2018');  // authentication
	curl_setopt($ch, CURLOPT_USERPWD,	$auth['lindenbaum_user'].':'.$auth['lindenbaum_pass']);  // authentication
	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}

####################################################################

function getData($url, $auth){
 
	$ch = curl_init($url);
  
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	curl_setopt($ch, CURLOPT_USERPWD,	$auth['lindenbaum_user'].':'.$auth['lindenbaum_pass']);  // authentication

	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:Inc!2018');  // authentication
	#curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($ch, CURLOPT_POSTFIELDS,0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}

####################################################################
