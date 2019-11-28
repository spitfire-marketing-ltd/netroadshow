<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");


$data = json_decode(file_get_contents("php://input"), TRUE);

$arrEventData = $data['eventData'];

//print_r($arrEventData);

if (!empty($data)) {
	
	if ($arrEventData["eventID"]!=null) {
	
		include (dirname(__FILE__).'/db_connect.php');
	
		$sql = "SELECT 		conferences.*, 
						(	SELECT count(attendees.attendeeID) as total from attendees WHERE attendees.eventID=conferences.eventID) as total,
						(	SELECT count(attendees.attendeeID) as total from attendees WHERE attendees.conferenceID=conferences.conferenceID) as pending,
						(	SELECT count(attendees.attendeeID) as responded from attendees WHERE attendees.conferenceID=conferences.conferenceID AND attendees.accepted='Yes') as responded,
						(	SELECT count(attendees.attendeeID) as responded from attendees WHERE attendees.conferenceID=conferences.conferenceID AND attendees.attended='Yes') as attended,
						(	SELECT count(attendees.attendeeID) as total from attendees WHERE attendees.conferenceID=conferences.conferenceID AND attendees.invited='Yes') as invited 
				FROM conferences where eventID='".mysqli_real_escape_string($conn,$arrEventData["eventID"])."'
				Order By eventTimeUK ASC";
			
		//print $sql;
		
		$arr_tableData = array(); 

		$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		while($rc=mysqli_fetch_assoc ($rs)) {
			$rc["expanded"]=0;
			$rc["confID"]=str_replace('conf:','',$rc["LBConfID"]);			
			$rc["active"]=false;
			$arr_tableData[] = $rc;
		}
	 
		echo "[";
		$data = "";
	 
		foreach ($arr_tableData as $key => $objectdata) {
		
			$data.= json_encode($objectdata).",";
		}
	 
		echo trim($data," ,")."]";
		
	
		
		mysqli_close($conn);
		
	} else {
		echo "NO VALIDE ID";
	}
	
} else {
	echo "NO PAYLOAD";
}

exit();
?>