<?php //<!-- This file contains all connections to the mysql database: --> ?>
<?php
// This function returns an array of existing devices, usfull to fill dropdown combobox: 
function getDropDownData()
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//send query, receive output and put data into array:
		$sql = "SELECT * FROM CONFIG;";
		$data;		
		foreach ($conn->query($sql) as $row)
		{
			$data[] = $row['FreezerName'];
		}
		return $data;	
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}
//This function returns an array containing all active MAC adresses:
function getMacFromDatabase()
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//send query, receive output and put data into array:
		$sql = "SELECT * FROM CONFIG;";
		$data;		
		foreach ($conn->query($sql) as $row)
		{
			$data[] = $row['MAC'];
		}
		return $data;	
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//returns max en min preset values for given freezer:
function getMaxMinValuesFromFreezer($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//send query, receive output and put data into array:
		$sql = "SELECT * FROM CONFIG WHERE FreezerName='" . $device . "';";

		$data;		
		foreach ($conn->query($sql) as $row)
		{
			$data['minTemp'] = $row['minTemp'];
			$data['maxTemp'] = $row['maxTemp'];
		}
		return $data;	
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//this function returns an array with data from latest table row, containing temperature, relais state and timestamp:
function getRecentTemperature($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//send query, receive output and put data into array:
		$sql = "SELECT * FROM " . $device . " ORDER BY timestamp DESC LIMIT 1;";
		$data;	
		foreach ($conn->query($sql) as $row)
		{
			$data['temp'] = $row['temp'];
			$data['relais'] = $row['relais'];
			$data['timestamp'] = $row['timestamp'];
		}
		
		//if is not set, fill with '?': 
		if(!isset($data))
		{
			$data['temp'] = 'No data!';
			$data['relais'] = '?';
			$data['timestamp'] = 'Waiting for data to be updated!';
		}
		
		return $data;
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//This function returns the lowest temperature value recorded in the last 60min:
function getLowestTemperature($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//send query, receive output and put data into array:
		$sql = "SELECT MIN(temp) FROM " . $device . " WHERE timestamp >= DATE_SUB(NOW(),INTERVAL 1 HOUR);";
		$data;	
		foreach ($conn->query($sql) as $row)
		{
			$data['temp'] = $row['MIN(temp)'];
		}
		
		//if is not set, fill with '?': 
		if(!isset($data))
		{
			$data['temp'] = '?';
		}
		
		return $data;
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//This function returns the highest temperature value recorded in the last 60min:
function getHighestTemperature($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//send query, receive output and put data into array:
		$sql = "SELECT MAX(temp) FROM " . $device . " WHERE timestamp >= DATE_SUB(NOW(),INTERVAL 1 HOUR);";
		$data;	
		foreach ($conn->query($sql) as $row)
		{
			$data['temp'] = $row['MAX(temp)'];
		}
		
		//if is not set, fill with '?': 
		if(!isset($data))
		{
			$data['temp'] = '?';
		}
		
		return $data;
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//This function adds a new device to the database: 
function addDevice($device,$IP,$PORT,$MAC,$TempLow,$TempHigh)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	//Read from dropdowndata if device already exists: (prevent double device names!):
	$deviceArr = getDropDownData();	
	foreach($deviceArr as $existingDevices)
	{
		if($existingDevices == $device)
		{		
			return "false"; //if device with given $device name already exists, return false;
		}
	}
	
	//if device doesn't already exists connect to DB:
	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
		//check if table with given devicename already exists:
		$sql = "SHOW TABLES LIKE '" . $device . "';";
		$rowCount = 0;
		foreach ($conn->query($sql) as $row)
		{
			$rowCount++; //count if table exists with currently given '$device' name;
		}
		
		//if device table doesn't exists make a new one: 
		if($rowCount <= 0)
		{
			$state = addDeviceTable($device); //use addDevice function to do this;
			if($state != "true") //if table couldn't be created, return false;
				return "false";
		}
		
		//	if table is created and devicename doesn't exists, insert the new device into the CONFIG table: 
		//	--> this CONFIG table is used by my C++ server software to know the active devices.
		$sql = "INSERT INTO CONFIG SET FreezerName='" . $device . "', IP='" . $IP . "', PORT=" . $PORT . ", MAC='" . $MAC . "', minTemp=" . $TempLow . ", maxTemp=" . $TempHigh . ";";
		$conn->exec($sql);
		
		return "true";
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

//This function adds a device table used to store the actual data.
function addDeviceTable($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;
	
	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
		//Create sql query and send to db: --> add 1 table with given '$device'	as table name: 	
		$sql = "CREATE TABLE " . $device . " (temp float, relais bool, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
		$conn->exec($sql);
		
		return "true";
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
		return false;
	}
}

//This function deletes an active device only from the CONFIG table, it DOESN'T delete the actual table that stores the measurements!
function deleteDevice($device)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 
		//Create sql query and send to db: --> Deletes all rows where FreezerName = $device: --> this is why devicenames has to be unique!!!	 
		$sql = "DELETE FROM CONFIG WHERE FreezerName='" . $device . "';";
		$conn->exec($sql);
		
		return "true";
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}
//This function drops a table, all data will be lost!!!
function deleteTable($tableName)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;

	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 
		//Create sql query and send to db: --> Drops a table, all containing data wil be lost!	 
		$sql = "DROP TABLE " . $tableName . ";";
		$conn->exec($sql);
		
		return "true";
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

function getAllInactiveTables()
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;
	
	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 
		//Create sql query and send to db: --> Drops a table, all containing data wil be lost!	 
		$sql = "Show TABLES;";	
		$data;		
		foreach ($conn->query($sql) as $row)
		{
			//Skip contacts and config table:
			if($row[0] != "CONFIG" && $row[0] != "CONTACTS")
				$data[] = $row[0];
		}
		
		//remove active tables: 
		$activeTables = @getDropDownData();
		
		if(count($activeTables) <= 0)
			return $data;
		
		$result = array_diff($data, $activeTables);
		
		return $result;
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

function backupTable($tableName)
{
	include 'mysqlLogin.php'; // This file contains the ip, user and password for the database;
	
	try 
	{
		//connect to database using values from the included mysqlLogin.php: 
		$conn = new PDO("mysql:host=$servername;dbname=monitoringTest", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 
		//Create sql query and send to db: --> Drops a table, all containing data wil be lost!	 
		$sql = "Show TABLES;";	
		
		$table_name = $tableName;
		$backup_file  = "/srv/ftp/mysqlBackup/" . $tableName . ".csv";
		$sql = "SELECT * INTO OUTFILE '" . $backup_file . "' FIELDS TERMINATED BY ';' FROM " . $table_name . ";";
		$conn->exec($sql);
		
		return "true";
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}
}

?>