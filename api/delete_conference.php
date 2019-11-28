<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrForDeletion = $data['forDeletion'];
$api_url = 'https://www.incomm.online/conference/rest3/';

//print_r($arrForDeletion);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');

	foreach ($arrForDeletion as $entry) {
		
		//print_r($entry);
		
		// remove conference from LB
		$sql = "SELECT LBConfID from conferences WHERE
				
						 conferenceID = '".mysqli_real_escape_string($conn,$entry["conferenceID"])."'"; 
		
		$rs = mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		while($rc=mysqli_fetch_assoc ($rs)) {
			
				$auth['lindenbaum_user']="sc@spitfiremarketing.co.uk";
				$auth['lindenbaum_pass']="S@c#346834!";
				$result =  postData($api_url.'reservations/'.$rc["LBConfID"].'/', null , $auth , "DELETE");
		}
		
		
		$sql = "DELETE from conferences WHERE
				
						 conferenceID = '".mysqli_real_escape_string($conn,$entry["conferenceID"])."'"; 
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
	}	

	echo "OK";

	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}

####################################################################

function postData($url, $post, $auth, $method="POST", $mode="json" ){

	$ch = curl_init($url);
  
  
	if ($mode=="json")
		$headers= array('Accept: application/json','Content-Type: application/json'); 
	else
		$headers= array('Accept: application/xml','Content-Type: application/xml'); 
		
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:Inc!2018');  // authentication
	curl_setopt($ch, CURLOPT_USERPWD,	$auth['lindenbaum_user'].':'.$auth['lindenbaum_pass']);  // authentication
	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);  
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}

####################################################################

?>