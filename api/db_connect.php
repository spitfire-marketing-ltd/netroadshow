<?php
   include ("config.php");

    $url_connect=$globalConfig['dbhost'];
    $db_connect=$globalConfig['dbuser'];
    $db_password=$globalConfig['dbpass'];
    $db_name=$globalConfig['dbase'];
	

   
    $conn = mysqli_connect($url_connect, $db_connect, $db_password, $db_name);

	if (!$conn) {
		echo "Error: Unable to connect to MySQL." . PHP_EOL;
		echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
		exit;
	}
	
	
?>
