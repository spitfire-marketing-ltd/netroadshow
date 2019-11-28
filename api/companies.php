<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');

include (dirname(__FILE__).'/db_connect.php');

$sql = "SELECT * from companies";
$arr_tableData = array();

$rs=mysqli_query($conn, $sql) or die(mysql_error().$sql);
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