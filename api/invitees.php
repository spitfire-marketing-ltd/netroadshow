<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');

$arr_users = array(		1 => array (	"name" => "John Smith", 
										"company"=> "Merril", 
										"email" => "jsmith@aol.com",
										"phone" => "023 8044 2640",
										"priority" => "low",
										"usertype" => "Participant",
										"usergroup" => "2",
										"attendeeID" => 1,
										"invitedby" => array(
															"name" => "John Smith",
															"company" => "Merril",
															"phone" => "002380442640",
															"area" => "+44",
															"email" => "sc@spitfiremarketing.co.uk"
															),
										
										),
					
 );
 
 echo "[";
 $data = "";
 for ($n=1; $n<25;$n++) {
	foreach ($arr_users as $key => $objectdata) {
	
		$objectdata["attendeeID"]=$n;
		$objectdata["phone"]="023 8044 ".str_pad($n,4,"0");
		$data.= json_encode($objectdata).",";
	}
 }
echo trim($data," ,")."]";

exit();
?>