<?php


header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Post:*');

require(dirname(__FILE__)."/common.php");

$data = json_decode(file_get_contents("php://input"), TRUE);

if (!empty($data)) {

	include (dirname(__FILE__).'/db_connect.php');

	$arrCompanyData = $data['companyData'];

	//print_r($arrCompanyData);

	if ($arrCompanyData["companyID"]==null) {
		//echo "INSERT".chr(10);
		$typeDef 	= array("s","s","s","s","s","s","s","s","s","s","s");
		sql_insert ($conn, $arrCompanyData, $typeDef, "companies") ;
		
		echo mysqli_insert_id ( $conn ) ;
		
		
	} else {
		//echo "UPDATE".chr(10);
		$sql = "UPDATE companies SET

						companyName ='".mysqli_real_escape_string($conn,$arrCompanyData["companyName"])."',
						emailAddress ='".mysqli_real_escape_string($conn,$arrCompanyData["emailAddress"])."',
						language ='".mysqli_real_escape_string($conn,$arrCompanyData["language"])."',
						dataRetention ='".mysqli_real_escape_string($conn,$arrCompanyData["dataRetention"])."',
						passwordFrequency ='".mysqli_real_escape_string($conn,$arrCompanyData["passwordFrequency"])."',
						accountStatus ='".mysqli_real_escape_string($conn,$arrCompanyData["accountStatus"])."',
						logo ='".mysqli_real_escape_string($conn,$arrCompanyData["logo"])."',
						primaryColor ='".mysqli_real_escape_string($conn,$arrCompanyData["primaryColor"])."',
						secondaryColor ='".mysqli_real_escape_string($conn,$arrCompanyData["secondaryColor"])."',
						LBcustomerID ='".mysqli_real_escape_string($conn,$arrCompanyData["LBcustomerID"])."'

				WHERE companyID = '".mysqli_real_escape_string($conn,$arrCompanyData["companyID"])."'";
		
		mysqli_query($conn, $sql) or die(mysqli_error($conn).$sql);
		
		echo $arrCompanyData["companyID"];
		
	}


	mysqli_close($conn);
} else {
	echo "NO PAYLOAD";
}


?>
