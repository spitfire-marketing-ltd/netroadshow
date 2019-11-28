<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

include (dirname(__FILE__).'/db_connect.php'); 

$data = json_decode(file_get_contents("php://input"), TRUE);

//print_r($data);

$eventID = $data["eventID"];
$conferenceID = $data["conferenceID"];

$sql = "SELECT  attendees.*, 
		(SELECT CONCAT(conferences.startTime,' - ',conferences.endTime) FROM conferences WHERE conferenceID = attendees.firstChoice) as firstChoiceTIME,
		(SELECT CONCAT(conferences.startTime,' - ',conferences.endTime)  FROM conferences WHERE conferenceID = attendees.secondChoice) as secondChoiceTIME,
		(SELECT CONCAT(conferences.startTime,' - ',conferences.endTime)  FROM conferences WHERE conferenceID = attendees.thirdChoice) as thirdChoiceTIME,
		(SELECT CONCAT('fullName',':',invitees.firstName,' ',invitees.lastName,'|','companyName',':',invitees.companyName,'|','emailAddress',':',invitees.emailAddress,'|','contactNumber',':',invitees.contactNumber,'|','areaCode',':',invitees.areaCode)  FROM invitees WHERE attendees.inviteeID = invitees.inviteeID) as invitedByOBJECT		
		FROM attendees, conferences ".
		(($eventID) ?  "WHERE attendees.eventID='".mysqli_real_escape_string($conn,$eventID)."' ":"")." ".
		(($conferenceID) ?  "and attendees.conferenceID='".mysqli_real_escape_string($conn,$conferenceID)."' ":"")." ".
		"Group By attendees.attendeeID";
		
		
$arr_tableData = array();

$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
while($rc=mysqli_fetch_assoc ($rs)) {
	$rc["fullName"] = $rc["firstName"]." ".$rc["lastName"];
	$rc["invitedBy"] = objectify(explode("|",$rc["invitedByOBJECT"]));	
	unset($rc["invitedByOBJECT"]);
	$arr_tableData[] = $rc;
}

 
 echo "[";
 $data = "";
 
	foreach ($arr_tableData as $key => $objectdata) {
	
		$data.= json_encode($objectdata).",";
	}
 
echo trim($data," ,")."]";

mysqli_close($conn);
exit();

function objectify($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $value) {
			//print($value);
			$arrtmp = explode(":",$value);		
			//print_r( $arrtmp);
			if (count($arrtmp)>1) {	
				unset($array[$key]);
				$array[$arrtmp[0]] = $arrtmp[1];
			}
        }
    } else {
        return $array;
    }
    return (object) $array;
}
?>