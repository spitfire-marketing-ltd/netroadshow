<?php
$sid = session_start();

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*'); 
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

define("JWT_KEY", "nrs-1234");

date_default_timezone_set("Europe/London") ;

require_once("JWT.php");
require(dirname(__FILE__).'/postmark.php');

$postmark = new Postmark("d7a37e56-a21b-4282-a067-027a7750e95b","service@incommglobalevents.com");


$data = json_decode(file_get_contents("php://input"), TRUE);

//print_r($data);
$error= array("status" => "FAILED"); 


$token = $data['token'];

if (!empty($token)) {
	
	$arrUserData = validate_id_token($token);
	//print_r($arrUserData);
	//print_r($arrUserData->user->email);
	
	if($arrUserData->user->email!="") {
	
		include (dirname(__FILE__).'/db_connect.php');

		$sql = "SELECT 	users.*,
						(select companies.companyName from companies WHERE users.clientID = companies.companyID) as companyName,
						(select companies.accountStatus from companies WHERE users.clientID = companies.companyID) as accountStatus,
						(select companies.passwordFrequency from companies WHERE users.clientID = companies.companyID) as passwordFrequency
				FROM 	users 
				WHERE 	userName ='".mysqli_real_escape_string($conn,$arrUserData->user->email)."'";
						
		
		$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		if (mysqli_num_rows($rs)==1) { // rudimentary SUCCESS
		
			$rc=mysqli_fetch_assoc ($rs);
			//print_r($rc);
			//
			//echo 'P'.$rc["passwordFrequency"].'M';
			
			if ($rc["accountStatus"]=="Live") {
			
				// Update Login Info
				
				// Notify User		

				$newPass = randomPassword(10,1,"lower_case,upper_case,numbers,special_symbols");
				
				//print_r($newPass);
				
				$filename = dirname(__FILE__).'/emails/user-invitation.html';
				$handle = fopen($filename, "r");
				$htmlmessage = fread($handle, filesize($filename));
				$htmlmessage = str_replace("%FIRSTNAME%",$rc["firstName"],$htmlmessage);
				$htmlmessage = str_replace("%EMAIL%",$rc["userName"],$htmlmessage);
				$htmlmessage = str_replace("%PASSWORD%",$newPass[0],$htmlmessage);
				
				$result =  $postmark->to($rc["userName"]);
				$result =  $postmark->from("service@incommglobal.com");
				$result =  $postmark->subject("User Invitaion");
				$result =  $postmark->plain_message(strip_tags($htmlmessage));
				$result =  $postmark->html_message($htmlmessage);
				$result =  $postmark->send();
				
				// Package Data
				$arr_tableData = array();

				
				$arr_tableData[] = $rc;
								
				$data = "";
			 
				foreach ($arr_tableData as $key => $objectdata) {
				
					$data.= json_encode($objectdata).",";
				}
			 
				echo trim($data," ,");//."]";
			
			} else {
			
				$error["token"] = "ACCOUNT SUSPENDED";	
				$error["status"] = "Account has been suspended";			
				
				echo json_encode($error);
			}
			
			mysqli_close($conn);
		} else {
			$error["token"] = "NO USER";	
			$error["status"] = "Incorrect login credentials";	
			echo json_encode($error);
		}
	}
	
	$arr_user = array (	"auth" => true, 
						"token"=> $token, 
						"username" => "Steve Cornes",
						"company" => "Incomm Global Ltd",
						"reset_required" => false,
						"first_time" => true,
						"companyID" => 40,
						"userID" => 28,
						"usergroup" => 2,
	);

	//echo json_encode($arr_user);
} else {
	echo "NO PAYLOAD";
}
exit();


 
 function randomPassword($length,$count, $characters) {
 
// $length - the length of the generated password
// $count - number of passwords to be generated
// $characters - types of characters to be used in the password
 
// define variables used within the function    
    $symbols = array();
    $passwords = array();
    $used_symbols = '';
    $pass = '';
 
// an array of different character types    
    $symbols["lower_case"] = 'abcdefghijklmnopqrstuvwxyz';
    $symbols["upper_case"] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $symbols["numbers"] = '1234567890';
    $symbols["special_symbols"] = '!?~@#-_+<>[]{}';
 
    $characters = explode(",",$characters); // get characters types to be used for the passsword
    foreach ($characters as $key=>$value) {
        $used_symbols .= $symbols[$value]; // build a string with all characters
    }
    $symbols_length = strlen($used_symbols) - 1; //strlen starts from 0 so to get number of characters deduct 1
     
    for ($p = 0; $p < $count; $p++) {
        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $symbols_length); // get a random character from the string with all characters
            $pass .= $used_symbols[$n]; // add the character to the password string
        }
        $passwords[] = $pass;
    }
     
    return $passwords; // return the generated password
}
 
//$my_passwords = randomPassword(10,1,"lower_case,upper_case,numbers,special_symbols");
 
//print_r($my_passwords);
?>