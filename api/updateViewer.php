<?php 
session_start(); header("Cache-control: private");

date_default_timezone_set ("Europe/London" );

error_reporting(E_ERROR); //E_ERROR | E_WARNING | E_PARSE
ini_set("display_errors", 1);

$api_url = 'https://www.incomm.online/conference/rest3/';

$eid_entry	= $_POST["eid"];	

if ($_SESSION["LINDENBAUM"] == 0) {

	if ($_SESSION["EVENT:".$eid_entry]["viewconnect"] === TRUE) {
	
		$_SESSION["LINDENBAUM"] = 1;
		session_write_close();
		session_start();
		
		include("../../database/db_connect.php"); 	
		
		$nl = array('/n',chr(10),chr(13),'/n/r');
		
		
	//	echo $eid_entry;
		
		$rs = mysql_query("SELECT conference_ID, user_list,event_title, chat_data, accountname, accountpass from conferences WHERE event_id='".$eid_entry."'");
		$rc = mysql_fetch_array($rs) or die("Unable to collect conference ".$eid_entry." data!");
		$arr_chat_data = json_decode($rc["chat_data"],1);	
		
		$lindenbaum_user = $rc["accountname"];
		$lindenbaum_pass = $rc["accountpass"];
		
		$topic = $rc["event_title"];
		
		$lindenbaum_conf_id_data = $rc["conference_ID"];	
		$arr_lindenbaum_conf_id  =  unserialize($lindenbaum_conf_id_data);	
		
		
		
		//print_r($arr_chat_data);
		
		foreach ($arr_chat_data as $key => $value) {
		
			$chat_data.='<div class="chat_entry '.(($_SESSION["EVENT:".$eid_entry]["chatname"] == $value["author"]) ? "you" : "").'"> <span class="author ">'.$value["author"].'<span class="datetime ">'.$value["datetime"].' </span></span> &raquo; '.($value["message"]).'</div>';
			//$chat_data.='<div class="chat_entry you"><span class="author">'.$value["author"].' said</span> &raquo; '.$value["message"].'</div>';
		}
		
		
		$rsusers = mysql_query("SELECT * from conference_users WHERE parent_id='".$eid_entry."' and
																	 user='".mysql_real_escape_string($_SESSION["EVENT:".$eid_entry]["chatname"])."' and
																	 ip = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."';") or die(mysql_erro());
		//echo "SELECT * from conference_users WHERE parent_id='".$eid_entry."' and user='".mysql_real_escape_string($_SESSION["EVENT:".$eid_entry]["chatname"])."' and ip = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."';";
		//echo mysql_num_rows($rsusers);
		
		if (mysql_num_rows($rsusers)==0) {																
		 
			$sql = "INSERT into conference_users SET 
					parent_id = '".$eid_entry."',
					user='".mysql_real_escape_string($_SESSION["EVENT:".$eid_entry]["chatname"])."',
					designation='".mysql_real_escape_string($_SESSION["EVENT:".$eid_entry]["designation"])."',
					ip = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."',
					timestamp ='".date("Y-m-d H:i:s")."'";
			//echo $sql;
			mysql_query($sql) or die(mysql_error());	
		}
		
		
		$usersql = "update conference_users SET 				
					timestamp ='".date("Y-m-d H:i:s")."' where 
						parent_id='".$eid_entry."' and
						user='".mysql_real_escape_string($_SESSION["EVENT:".$eid_entry]["chatname"])."' and
						ip = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."';";
		//echo $usersql;
		mysql_query($usersql);
		
		$sql = "DELETE FROM conference_users WHERE timestamp < (NOW() - INTERVAL 10 SECOND)";
		//echo $sql;
		mysql_query($sql) or die(mysql_error());
		
		$rsusers = mysql_query("SELECT * from conference_users WHERE parent_id='".$eid_entry."'");
		while ($rcuser = mysql_fetch_array($rsusers)) {
			
			$user_data.='<div class="online_entry"><span class="user ">'.$rcuser["user"].'</span>'.(($rcuser["designation"]) ? '<span class="designation"> | '.$rcuser["designation"].'</span>':'').'</div>';
		}
		
		/*
		GREEN - Connected/Line open
		AMBER - Connected Parked
		GREY - Connected/Muted
		RED - Disconnected
		*/
		
		if (is_array($arr_lindenbaum_conf_id)) {
			foreach ($arr_lindenbaum_conf_id as $key => $value) {
				$conference_ID = html_entity_decode(utf8_encode(mysql_real_escape_string($value["lindenbaum_conf_id"])));
				$result =  getData($api_url.'/conferences/'.$conference_ID.'/participants/?first-item=0&max-items=999');
				$a_res[$key] = json_decode($result,1);
			}
			$arr_result = array_merge((array)$a_res[0],(array)$a_res[1],(array)$a_res[2],(array)$a_res[3],(array)$a_res[4],(array)$a_res[5],(array)$a_res[6],(array)$a_res[7],(array)$a_res[8],(array)$a_res[9]);
		} else {
			$conference_ID = $rc["conference_ID"];
			$result =  getData($api_url.'/conferences/'.$conference_ID.'/participants/?first-item=0&max-items=999');
			$arr_result = json_decode($result,1);
		}

		
		//print_r($arr_result);
		
		
		$no_participants = count($arr_result);
		
		//$topic = "TEST";
		$online = 0;
		
		foreach ($arr_result as $key => $participant) {
		
			if (($participant["audio-state"]!="CONNECTION_LOST") && ($participant["audio-state"]!="DISCONNECTED") && ($participant["audio-state"]!="HANGUP")) {
		
				switch ($participant["audio-state"]) {
					case "CONFERENCING" : $audiostate="green";$online++;break;
					case "MUTED" : $audiostate="grey";$online++;break;
					case "PARKED" : $audiostate="orange";$online++;break;
					//default : $audiostate="red";break;
				}
				
				if ($participant["company"]=="") {			
					$mydata = explode(':', $participant["label"]);
					$name = $mydata[0];
					$company = $mydata[1];
				} else {
					$company = $participant["company"];
					$name = $participant["label"];
				}
				$participant_data .= '<div class="entry" data-sid="'.(($participant["rts-state"]!="OFF") ? $participant["rts-index"]:"9999").'"><div class="name"><span><span class="status '.$audiostate.'"></span>'.$name.'</span></div><div class="company"><span>'.$company.'</span></div><!--<div class="phone"><span>'.$participant["caller-phone"].'</span></div>--><div class="question '.(($participant["rts-state"]=="ON") ? "yes":"").' '.(($participant["rts-state"]=="ACCEPTED") ? "yes green" : "").'"><span class="icon"></span></div></div>';
			} 
		}
		
		$status = "OK";
		
		$_SESSION["LINDENBAUM"] = 0;
		session_write_close();
		mysqli_close($link);
		
		echo json_encode(array( "status" 	=>$status,														
								"eid" 		=> $eid_entry,
								"chat"	=> rawurlencode($chat_data),
								"participants"	=> rawurlencode($participant_data),
								//"no_participants" => rawurlencode($online.'/'.$no_participants),
								"no_participants" => rawurlencode($online),
								"topic" => rawurlencode($topic),
								"user_data" => rawurlencode($user_data),
								//"user_db" => $usersql
								));		
		

	} else {
		echo "NOT AUTHORISED:".$_SESSION["EVENT:".$eid_entry]["viewconnect"];
	}
}

####################################################################

function getData($url){

	global $lindenbaum_user, $lindenbaum_pass;
 
	$ch = curl_init($url);
  
	$headers= array('Accept: application/json','Content-Type: application/json'); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_USERPWD,	$lindenbaum_user.':'.$lindenbaum_pass);  // authentication
	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:eventregistration');  // authentication
	#curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($ch, CURLOPT_POSTFIELDS,0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}

####################################################################

function sendPostData($url, $post, $mode="json"){

	global $lindenbaum_user, $lindenbaum_pass;
 
	$ch = curl_init($url);
  
  
	if ($mode=="json")
		$headers= array('Accept: application/json','Content-Type: application/json'); 
	else
		$headers= array('Accept: application/xml','Content-Type: application/xml'); 
		
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_USERPWD,	$lindenbaum_user.':'.$lindenbaum_pass);  // authentication
	//curl_setopt($ch, CURLOPT_USERPWD,	'event registration:eventregistration');  // authentication
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
	$result = curl_exec($ch);
	curl_close($ch);  // Seems like good practice
	return $result;
}
####################################################################

?>