<?php
	include '/db-connect/devicesLib.php';
	
	function echoInactiveDB()
	{
		$Tables = getAllInactiveTables();
		sort($Tables);
		//rsort($Tables);
		foreach($Tables as $freezer)
		{
			$recentData = getRecentTemperature($freezer);
			$lowestData = null;
			$highstData = null;
			if(false) //enabling this can cause long loading times on advancedSettings page.
			{	
				$lowestData = getLowestTemperature($freezer);
				$highstData = getHighestTemperature($freezer);
			}
			
			$uri = 'drawChart.php?tableSelect=' . $freezer . '&queryTimeSpan=60';
			$uriTrash = 'advancedSettings.php?rmTable=' . $freezer;
			$uriBackup = 'advancedSettings.php?backupTable=' . $freezer;
			
			echo "<div class=\"Freezers\">";
			echo "<h2>" . $freezer . "</h2>";
		
			if($lowestData['temp'] != null)
			{	
				echo '<p id="minImg"></p>';
				echo '<p id="min">' . $lowestData['temp'] . '&deg;C</p>';
			}
			
			echo '<p id="recent">' . $recentData['temp'] . '&deg;C</p>';
			
			if($highstData['temp'] != null)
			{
				echo '<p id="maxImg"></p>';
				echo '<p id="max">' . $highstData['temp'] . '&deg;C</p>';
			}
			
			echo '<p id="time">' . $recentData['timestamp'] . '</p>';
			echo '<a id="backupImg" href=' . $uriBackup . '></a>';
			echo '<a id="TrashbinImg" href=' . $uriTrash . '></a>';
			echo '<a id="square" href=' . $uri . '></a>';
			echo "</div>";
		}
	}
	
	echoRemoveTableConfirmation();
	function echoRemoveTableConfirmation()
	{
		if(isset($_GET['rmTable']) && !isset($_GET['rmConfirm']) ) 
		{
			$tableToRemove = $_GET['rmTable'];
			echo "
				<script type='text/javascript'>
					if (confirm('If you continue, all data from this table is removed and unavailable in the future!')) 
					{
						var currentURI = window.location.href ;
						var addConfirmation = '&rmConfirm=true';
						var uri = currentURI.concat(addConfirmation);
						window.location = uri;
					}else{
						window.location = 'advancedSettings.php';
					}
				</script>";
		}
	}
	
	removeTableWhenConfirmed();
	function removeTableWhenConfirmed()
	{
		if(isset($_GET['rmConfirm']))
		{
			if($_GET['rmConfirm'] == 'true')
			{	
				deleteTable($_GET['rmTable']);
				echo "
					<script type='text/javascript'>
						alert('Table removed succesfully.');
					</script>";
			}
		}
	}
	
	backupTableWhenConfirmed();
	function backupTableWhenConfirmed()
	{
		if(isset($_GET['backupTable']))
		{
			backupTable($_GET['backupTable']);
			
			include '/db-connect/mysqlLogin.php';
			
			$file_name = $_GET['backupTable'] . ".csv" ;
			$file_url = "ftp://" . $servername . '/mysqlBackup/' . $file_name;
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"".$file_name."\""); 
			readfile($file_url);	
			exit;
		}
	}
?>

<!-- including path to sylesheets for a nice design: -->
<link rel="stylesheet" type="text/css" href="CSS/menu.css">
<link rel="stylesheet" type="text/css" href="CSS/main.css">
<link rel="stylesheet" type="text/css" href="CSS/advancedSettings.css">
<link rel="stylesheet" type="text/css" href="CSS/screenSize.css">

<html>
	<head>
		<title>Diepvries monitoring:</title>
		<h1>Advanced Settings:</h1>
		
		<!-- Menu buttons: -->
		<div class="menu">
			<a id="" href='index.php'>Home.</a>
			<a href='index.php?addDevice=true'>Add new device.</a>
			<a href='index.php?rmDevice=true'>Disable device.</a>
			<a href='advancedSettings.php'>Advanced settings.</a>
		</div>
	</head>
	<body>
		<div class="inactiveDB">
			<h2 id="InactiveDB_h2"><u>Inactive databases:</u></h2>
			<?php echoInactiveDB(); ?>
		</div>
	</body>
</html>