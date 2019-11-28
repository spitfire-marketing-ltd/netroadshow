<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);
$arrEventData = $data['eventData'];

if (!empty($data)) {
	
	if ($arrEventData["eventID"]!=null) {
	
		include (dirname(__FILE__).'/db_connect.php');
	
		$sql = "SELECT 	events.*,
						(select users.firstName from users where users.userID = `events`.userID) as organiser,
						(select companies.logo from companies where companies.companyID = `events`.companyID) as brandLogo,
						(select companies.primaryColor from companies where companies.companyID = `events`.companyID) as brandPC,
						(select companies.secondaryColor from companies where companies.companyID = `events`.companyID) as brandSC
				FROM events where eventID='".mysqli_real_escape_string($conn,$arrEventData["eventID"])."'";
		
		$arr_tableData = array();

		$rs=mysqli_query($conn, $sql) or die(mysql_error().$sql);
		while($rc=mysqli_fetch_assoc ($rs)) {
			$rc["speakers"] = json_decode($rc["speakers"],1);
			$rc["preferredPC"] = (($rc["useCompanyBranding"]) ? $rc["brandPC"] : $rc["primaryColor"]);
			$rc["preferredSC"] = (($rc["useCompanyBranding"]) ? $rc["brandSC"] : $rc["secondaryColor"]);
			$rc["preferredLogo"] = (($rc["useCompanyBranding"]) ? $rc["brandLogo"] : $rc["logoOverride"]);
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