<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");


$data = json_decode(file_get_contents("php://input"), TRUE);
$clientID = $data['clientID'];

include (dirname(__FILE__).'/db_connect.php');

$sql = "SELECT  users.userID,
				users.clientID,
				users.sessionID,
				users.userName,
				users.userPass,
				users.userGroup,
				users.isAdmin,
				users.pwLastChanged,
				users.firstName,
				users.lastName,
				users.emailAddress,
				users.contactNumber,
				users.areaCode,
				users.address1,
				users.address2,
				users.address3,
				users.address4,
				users.country,
				users.postcode,
				users.language,
				users.timezone,
				users.accountStatus,
				users.lastLog,

				(select companies.companyName from companies WHERE users.clientID = companies.companyID) as companyName
		FROM 	users
		WHERE clientID='".mysqli_real_escape_string($conn,$clientID)."'";
		
$arr_tableData = array();

$rs=mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
while($rc=mysqli_fetch_assoc ($rs)) {
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
?>