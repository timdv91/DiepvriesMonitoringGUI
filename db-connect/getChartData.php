<?php

	// Instead you can query your database and parse into JSON etc etc
	date_default_timezone_set('UTC');

	include 'mysqlLogin.php';

try 
{
    $conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		getTemp($conn);	
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}

function getTemp($conn) 
{
	$sql = $_GET['query'];
	$data;	
	$i = 0;
	foreach ($conn->query($sql) as $row)
	{
		$temp = (float)$row['temp'];
		$date = $row['timestamp'];
		
		$data[] = array($date,(float)$row['temp']);		
		$i++;
	}	
	
	$jsonTable = json_encode($data);
	echo $jsonTable;
}

function JSdate($in,$type){
    if($type=='date'){
        //Dates are patterned 'yyyy-MM-dd'
        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $in, $match);
    } elseif($type=='datetime'){
        //Datetimes are patterned 'yyyy-MM-dd hh:mm:ss'
        preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $in, $match);
    }
     
    $year = (int) $match[1];
    $month = (int) $match[2] - 1; // Month conversion between indexes
    $day = (int) $match[3];
     
    if ($type=='date'){
        return "Date($year, $month, $day)";
    } elseif ($type=='datetime'){
        $hours = (int) $match[4];
        $minutes = (int) $match[5];
        $seconds = (int) $match[6];

		return "Date($year, $month, $day, $hours, $minutes, $seconds)";
    }
}		
?>
