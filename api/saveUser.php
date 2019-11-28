<?php


header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__).'/postmark.php');

$postmark = new Postmark("d7a37e56-a21b-4282-a067-027a7750e95b","service@incommglobalevents.com");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrUserData = $data['userData'];
$md5pass=null;

//print_r($arrUserData);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');
	include (dirname(__FILE__).'/common.php');

	$arrUserData = $data['userData'];

	$lastLog = date("Y-m-d H:i:s");

	if ($arrUserData["userID"]==null) {
		//echo "INSERT".chr(10);
		
		if ($arrUserData["userPass"]) {
			$md5pass = md5($arrUserData["userPass"]);
			
			$userKey = makeKey($arrUserData["userName"], SERVERKEY, $md5pass);	
			$e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
			$LBPassword = $e->encrypt($arrUserData["userPass"], $userKey);
		}
		$arrUserData["lastLog"] = "2000-01-01 00:00:00";
		
		
		
		//print_r($arrUserData);
		
		$sql = "INSERT INTO users SET

						
						clientID ='".mysqli_real_escape_string($conn,$arrUserData["clientID"])."',
						sessionID ='".mysqli_real_escape_string($conn,$arrUserData["sessionID"])."',
						userName ='".mysqli_real_escape_string($conn,$arrUserData["userName"])."',".
						(($md5pass) ? "userPass ='".mysqli_real_escape_string($conn,$md5pass)."',":"").	
						(($md5pass) ? "pwLastChanged ='".mysqli_real_escape_string($conn,date("Y-m-d H:i:s"))."',":"").					
						(($md5pass) ? "LBPassword ='".mysqli_real_escape_string($conn,$LBPassword)."',":"").
						"userGroup ='".mysqli_real_escape_string($conn,$arrUserData["userGroup"])."',
						isAdmin ='".mysqli_real_escape_string($conn,$arrUserData["isAdmin"])."',
						firstName ='".mysqli_real_escape_string($conn,$arrUserData["firstName"])."',
						lastName ='".mysqli_real_escape_string($conn,$arrUserData["lastName"])."',
						emailAddress ='".mysqli_real_escape_string($conn,$arrUserData["emailAddress"])."',
						contactNumber ='".mysqli_real_escape_string($conn,$arrUserData["contactNumber"])."',
						areaCode ='".mysqli_real_escape_string($conn,$arrUserData["areaCode"])."',						
						address1 ='".mysqli_real_escape_string($conn,$arrUserData["address1"])."',
						address2 ='".mysqli_real_escape_string($conn,$arrUserData["address2"])."',
						address3 ='".mysqli_real_escape_string($conn,$arrUserData["address3"])."',
						address4 ='".mysqli_real_escape_string($conn,$arrUserData["address4"])."',
						country ='".mysqli_real_escape_string($conn,$arrUserData["country"])."',
						postcode ='".mysqli_real_escape_string($conn,$arrUserData["postcode"])."',
						language ='".mysqli_real_escape_string($conn,$arrUserData["language"])."',
						timezone ='".mysqli_real_escape_string($conn,$arrUserData["timezone"])."',
						lastLog ='".mysqli_real_escape_string($conn,"2000-01-01 00:00:00")."',						
						accountStatus ='".mysqli_real_escape_string($conn,$arrUserData["accountStatus"])."'";
		
		//echo $result;
		mysqli_query($conn, $sql); //or die(mysqli_error($conn).mysqli_errno($conn));
		
		
		
		if (mysqli_errno($conn) == 1062) {
				$error["token"] = "DUPE";	
				$error["status"] = "Duplicate Entry: User already exists";			
				$error["code"] = mysqli_errno($conn);
				echo json_encode($error);
		} else {
		
				// Notify User			
				
				$filename = dirname(__FILE__).'/emails/user-invitation.html';
				$handle = fopen($filename, "r");
				$htmlmessage = fread($handle, filesize($filename));
				$htmlmessage = str_replace("%FIRSTNAME%",$arrUserData["firstName"],$htmlmessage);
				$htmlmessage = str_replace("%EMAIL%",$arrUserData["userName"],$htmlmessage);
				$htmlmessage = str_replace("%PASSWORD%",$arrUserData["userPass"],$htmlmessage);
				
				$result =  $postmark->to($arrUserData["userName"]);
				$result =  $postmark->from("service@incommglobal.com");
				$result =  $postmark->subject("User Invitaion");
				$result =  $postmark->plain_message(strip_tags($htmlmessage));
				$result =  $postmark->html_message($htmlmessage);
				$result =  $postmark->send();
				
				// Return data
		
				$error["token"] = "SUCCESS";	
				$error["status"] = "New user created";			
				$error["userID"] = mysqli_insert_id ( $conn );			
				$error["code"] = mysqli_errno($conn);
				echo json_encode($error);
			
		}
		
	} else { 
		//echo "UPDATE".chr(10);
		if ($arrUserData["userPass"]) {
			$md5pass = md5($arrUserData["userPass"]);
			$userKey = makeKey($arrUserData["userName"], SERVERKEY, $md5pass);
			$e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
			$LBPassword = $e->encrypt($arrUserData["userPass"], $userKey);
		}
		$sql = "UPDATE users SET

						
						clientID ='".mysqli_real_escape_string($conn,$arrUserData["clientID"])."',
						sessionID ='".mysqli_real_escape_string($conn,$arrUserData["sessionID"])."',
						userName ='".mysqli_real_escape_string($conn,$arrUserData["userName"])."',".
						(($md5pass) ? "userPass ='".mysqli_real_escape_string($conn,$md5pass)."',":"").	
						(($md5pass) ? "pwLastChanged ='".mysqli_real_escape_string($conn,date("Y-m-d H:i:s"))."',":"").	
						(($md5pass) ? "LBPassword ='".mysqli_real_escape_string($conn,$LBPassword)."',":"").					
						"userGroup ='".mysqli_real_escape_string($conn,$arrUserData["userGroup"])."',
						isAdmin ='".mysqli_real_escape_string($conn,$arrUserData["isAdmin"])."',
						firstName ='".mysqli_real_escape_string($conn,$arrUserData["firstName"])."',
						lastName ='".mysqli_real_escape_string($conn,$arrUserData["lastName"])."',
						emailAddress ='".mysqli_real_escape_string($conn,$arrUserData["emailAddress"])."',
						contactNumber ='".mysqli_real_escape_string($conn,$arrUserData["contactNumber"])."',
						areaCode ='".mysqli_real_escape_string($conn,$arrUserData["areaCode"])."',						
						address1 ='".mysqli_real_escape_string($conn,$arrUserData["address1"])."',
						address2 ='".mysqli_real_escape_string($conn,$arrUserData["address2"])."',
						address3 ='".mysqli_real_escape_string($conn,$arrUserData["address3"])."',
						address4 ='".mysqli_real_escape_string($conn,$arrUserData["address4"])."',
						country ='".mysqli_real_escape_string($conn,$arrUserData["country"])."',
						postcode ='".mysqli_real_escape_string($conn,$arrUserData["postcode"])."',
						language ='".mysqli_real_escape_string($conn,$arrUserData["language"])."',
						timezone ='".mysqli_real_escape_string($conn,$arrUserData["timezone"])."',
						accountStatus ='".mysqli_real_escape_string($conn,$arrUserData["accountStatus"])."'						

				WHERE userID = '".mysqli_real_escape_string($conn,$arrUserData["userID"])."'"; 
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		
		
		$error["token"] = "SUCCESS";	
		$error["status"] = "User updated";			
		$error["userID"] = $arrUserData["userID"];			
		$error["code"] = mysqli_errno($conn);
		
		echo json_encode($error);
		
	}


	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

?>