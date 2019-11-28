<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

$data = json_decode(file_get_contents("php://input"), TRUE);

$countryCode = $data["countryCode"];

include (dirname(__FILE__).'/db_connect.php'); 

$sql = "SELECT tz, concat(tz,' [',UTC_offset,']') as timeZone from lookup_timezones where cc!='' and cc='".mysqli_real_escape_string($conn,$countryCode)."'";
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