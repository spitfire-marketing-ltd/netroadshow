<?php
$sid = session_start();

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*'); 
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

define("JWT_KEY", "nrs-1234");

date_default_timezone_set("UTC") ;

require_once("JWT.php");

$data = json_decode(file_get_contents("php://input"), TRUE);

//print_r($data);
$error= array("status" => "FAILED"); 


$token = $data['token'];

if (!empty($token)) {
	
	$arrUserData = validate_id_token($token);
	//print_r($arrUserData);
	//print_r($arrUserData->user->email);
	
	if($arrUserData->user->email!="" && $arrUserData->user->password!="") {
	
		include (dirname(__FILE__).'/db_connect.php');

		$sql = "SELECT 	users.*,
						(select companies.companyName from companies WHERE users.clientID = companies.companyID) as companyName,
						(select companies.accountStatus from companies WHERE users.clientID = companies.companyID) as accountStatus,
						(select companies.passwordFrequency from companies WHERE users.clientID = companies.companyID) as passwordFrequency
				FROM 	users 
				WHERE 	userName ='".mysqli_real_escape_string($conn,$arrUserData->user->email)."' AND
						userPass ='".mysqli_real_escape_string($conn,md5($arrUserData->user->password))."'";
						
		
		$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		if (mysqli_num_rows($rs)==1) { // rudimentary SUCCESS
		
			$rc=mysqli_fetch_assoc ($rs);
			unset($rc["LBPassword"]);
			
			//
			//echo 'P'.$rc["passwordFrequency"].'M';
			
			if ($rc["accountStatus"]=="Live") {
			
				// Update Login Info
				
				$lastLog = date("Y-m-d H:i:s");
				
				$sql = "UPDATE users SET					
							
							sessionID ='".mysqli_real_escape_string($conn,$sid)."',						
							lastLog ='".mysqli_real_escape_string($conn,$lastLog)."'						

					WHERE 	userName ='".mysqli_real_escape_string($conn,$arrUserData->user->email)."' AND
							userPass ='".mysqli_real_escape_string($conn,md5($arrUserData->user->password))."'";
				
				mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
				
				// Package Data
				$arr_tableData = array();

				
					$pwLastChanged = new DateTime($rc["pwLastChanged"]);
					$now = new DateTime(date("Y-m-d H:i:s"));
					$pwExpiries = new DateTime($rc["pwLastChanged"]);
					$pwExpiries = $pwExpiries->add(new DateInterval('P'.$rc["passwordFrequency"].'M'));
					$interval = $now->format("U")-$pwExpiries->format("U");	

					/*
					print_r($pwLastChanged).chr(10);
					print_r($pwExpiries).chr(10);
					echo $pwLastChanged->format("U").chr(10);
					echo $pwExpiries->format("U").chr(10);
					
					var_dump($interval);
					*/
					
					if ($interval > 0) 	{	
						$rc["reset_required"]=true;					
					} else {
						$rc["reset_required"]=false;					
					} 
					
					if ($rc["lastLog"] == "2000-01-01 00:00:00") {
						$rc["first_time"] = true;
					} else {
						$rc["first_time"]=false;
					}
					
					$rc["pwInterval"] = $interval;
					$rc["pwExpires"] = $pwExpiries;
					$rc["token"] = $token;					
					$rc["lastLog"] = $lastLog;
				
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
 
    $characters = split(",",$characters); // get characters types to be used for the passsword
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