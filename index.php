<!DOCTYPE html>

<!-- First part reloads webpage when device is added or removed: -->
<!-- This is done seperatley in javascript, this refreshes the changes in a faster then the default delay. -->
<script type="text/javascript">
	setInterval(function() 
	{	
		// Get saved data from sessionStorage, this stores javascript vars between files. Is destroyd when page closes.
		var data = sessionStorage.getItem('pageNeedsReload');		
		if(data == "true")
		{
			location.reload();
			sessionStorage.setItem('pageNeedsReload', 'false'); // Save data to sessionStorage to prevent continously reloads.
		}	
	},1000);
</script>

<!-- The PHP part shows the freezers list, loaded serverside from mysql database. -->
<!-- It also shows the form window to add or remove devices. -->
<?php
	//Include the deviceLib library, in this library all mysql connections are managed:
	include '/db-connect/devicesLib.php';
	
	//Reloads webpage each 5seconds, if the user is not inputting data:
	reloadPage();
	function reloadPage()
	{
		// only refresh page when user is not inputting data:
		if(!isset($_GET['addDevice']) && !isset($_GET['rmDevice']))
			header( "refresh:5;url=index.php" );
	}
	
	//Function is called on each page load, it loads all necessary data from the mysql database and echo's on webpage: 
	function echoFreezers()
	{
		$freezers = @getDropDownData();
		
		if(count($freezers) <= 0)
		{	
			echo "No freezers are curently set. Use the \"Add new device\" button to configurate one.";
			return;
		}
		foreach($freezers as $freezer)
		{
			$recentData = getRecentTemperature($freezer); // --> Contains also relais-state and timestamp.
			$lowestData = getLowestTemperature($freezer);
			$highstData = getHighestTemperature($freezer);
			
			$chartMax = $highstData['temp'] +5;
			$chartMin = $lowestData['temp'] -5;
			$uri = 'drawChart.php?tableSelect=' . $freezer . '&queryTimeSpan=60' . '&highTemp=' . $chartMax . '&lowTemp=' . $chartMin;
			$uriBackup = 'advancedSettings.php?backupTable=' . $freezer;
			
			//set different collors to divs:
			date_default_timezone_set('Europe/Brussels');//or change to whatever timezone you want		
			$FreezerTempRange = getMaxMinValuesFromFreezer($freezer);
			if(strtotime("-1 minutes", time()) >  strtotime($recentData['timestamp'])) //when connection probably failed and nu updates have been done for x min.
			{	
				echo "<div class=\"Freezers\" style=\"background-color: #df41f4\" >"; //purple
			}elseif($FreezerTempRange['maxTemp'] < $recentData['temp']) // when max temp range is passed.
			{
				echo "<div class=\"Freezers\" style=\"background-color: #f48841\" >"; //redorange
			}elseif($FreezerTempRange['minTemp'] > $recentData['temp']) // when min temp range is passed.
			{
				echo "<div class=\"Freezers\" style=\"background-color: #418ef4\" >"; //darkblue
			}
			elseif($recentData['relais'] == "0") // relais alert. //greenYellowish
			{	
				echo "<div class=\"Freezers\" style=\"background-color: #f4f441\" >";
			}else //when all is oki. //default color
			{	
				echo "<div class=\"Freezers\">";
			}
			
			echo "<h2>" . $freezer . "</h2>";
			echo '<p id="minImg"></p>';
			echo '<p id="min">' . $lowestData['temp'] . '&deg;C</p>';
			echo '<p id="recent">' . $recentData['temp'] . '&deg;C</p>';
			echo '<p id="maxImg"></p>';
			echo '<p id="max">' . $highstData['temp'] . '&deg;C</p>';
			echo '<p id="time">' . $recentData['timestamp'] . '</p>';
			echo '<a id="backupImg" href=' . $uriBackup . '></a>';
			echo '<a id="square" href=' . $uri . '></a>';
			echo "</div>";

		}
	}
	
	//Function code is executed when addDevice is set --> happens after clicking button.
	function echoAddDevice()
	{
		if(isset($_GET['addDevice'])) //this way we change page item without reloading the page;
		{
			echo '<div class="subMenu">';
			echo '<object data="addDevice.php">	Your browser doesn’t support the object tag. </object>'; 
			echo '</div>';
		}
	}
	
	//Function code is executed when rmDevice is set --> happens after clicking button.
	function echoRemoveDevice()
	{
		if(isset($_GET['rmDevice'])) //this way we change page item without reloading the page;
		{
			echo '<div class="subMenu">';
			echo '<object data="rmDevice.php">	Your browser doesn’t support the object tag. </object>';
			echo '</div>';
		}
	}
?>

<!-- including path to sylesheets for a nice design: -->
<link rel="stylesheet" type="text/css" href="CSS/menu.css">
<link rel="stylesheet" type="text/css" href="CSS/main.css">
<link rel="stylesheet" type="text/css" href="CSS/screenSize.css">

<html>
	<head>
		<title>Diepvries monitoring:</title>
		<h1>Overview:</h1>
		
		<!-- Menu buttons: -->
		<div class="menu">
			<a id="" href='index.php'>Home.</a>
			<a href='index.php?addDevice=true'>Add new device.</a>
			<a href='index.php?rmDevice=true'>Disable device.</a>
			<a href='advancedSettings.php'>Advanced settings.</a>
		</div>
	</head>
	<body>
		<h2 class="activeDB">Active devices:</h2>
		<!-- call php functions, functions are in php scope @ top of this file. Cleaner coding this way. -->
		<?php echoAddDevice(); ?>		<!-- Shows the add device window -->
		<?php echoRemoveDevice(); ?> 	<!--Shows the remove device window -->
		<?php echoFreezers(); ?> 		<!-- shows all devices set in database -->
	</body>
</html>