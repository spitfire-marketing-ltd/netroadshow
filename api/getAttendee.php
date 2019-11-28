<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");


$data = json_decode(file_get_contents("php://input"), TRUE);
$arrAttendeeData= $data['attendeeData'];

if (!empty($data)) {
	
	if ($arrAttendeeData["attendeeID"]!=null) {
	
		include (dirname(__FILE__).'/db_connect.php');
	
		$sql = "SELECT * from attendees where attendeeID='".mysqli_real_escape_string($conn,$arrAttendeeData["attendeeID"])."'";
		
		$arr_tableData = array();

		$rs=mysqli_query($conn, $sql) or die(mysql_error().$sql);
		while($rc=mysqli_fetch_assoc ($rs)) {			
			$arr_tableData[] = $rc;
		}
	 
		//echo "[";
		$data = "";
	 
		foreach ($arr_tableData as $key => $objectdata) {
		
			$data.= json_encode($objectdata).",";
		}
	 
		echo trim($data," ,");//."]";
		
	
		
		mysqli_close($conn);
		
	} else {
		echo "NO VALIDE ID";
	}
	
} else {
	echo "NO PAYLOAD";
}

exit();
?>