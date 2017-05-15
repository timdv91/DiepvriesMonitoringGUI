<?php
	
	session_start();
	
	if(isset($_GET['lowTemp']) && isset($_GET['highTemp']) && isset($_GET['queryTimeSpan']))
	{
		$_SESSION['lowTemp'] = $_GET['lowTemp'];
		$_SESSION['highTemp'] = $_GET['highTemp'];
	}
	
	if($_GET['queryTimeSpan'] <= 60)
	{
		$chartMin = $_SESSION['lowTemp'];
		$chartMax = $_SESSION['highTemp'];
	}else{
		$chartMin = -100;
		$chartMax = 100;
	}
	
	date_default_timezone_set('Europe/Brussels');

	//Load dropdown menu items from database: 
	include '/db-connect/devicesLib.php';
	$data = getDropDownData();
	
	if(!isset($_GET['dateFrom']))
	{
		$_GET['dateFrom'] = date("2017-01-01");
	}
	if(!isset($_GET['dateTo']))
	{
		$_GET['dateTo'] = date("Y-m-d");
	}
	if(!isset($_GET['tableSelect']))	
		$_GET['tableSelect'] = $data[0];
	if(!isset($_GET['queryTimeSpan']))
	{	$_GET['queryTimeSpan'] = "10";
		header("Refresh:0; url=drawChart.php?tableSelect=" . $_GET['tableSelect'] . "&queryTimeSpan=" . $_GET['queryTimeSpan']);
	}
	if(!isset($_GET['highTemp']))
	{
		$_GET['highTemp'] = 100;
	}
	if(!isset($_GET['lowTemp']))
	{
		$_GET['lowTemp'] = -100;
	}
?>

<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script type="text/javascript">  	
	
	// Load the Visualization API and the piechart package.
	google.charts.load('current', {'packages':['line']});
	// Set a callback to run when the Google Visualization API is loaded.
	google.charts.setOnLoadCallback(drawChart);
	  
	function drawChart() 
	{
		var $_GET=[];
		window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(a,name,value){$_GET[name]=value;});
		
		if($_GET['queryTimeSpan'] != "none")
			var uri = "db-connect/getChartData.php?query=SELECT * FROM " + $_GET['tableSelect'] + " WHERE timestamp BETWEEN timestamp(DATE_SUB(NOW(), INTERVAL " + $_GET['queryTimeSpan'] + " MINUTE)) AND timestamp(NOW());";
		else
			var uri = "db-connect/getChartData.php?query=SELECT * FROM " + $_GET['tableSelect'] + " where timestamp >= '" + $_GET['dateFrom'] + "' and timestamp <= '" + $_GET['dateTo'] + "';";

		var jsonData = $.ajax({
		  url: uri,
		  dataType: "json",
		  async: false
		  }).responseText;
	
		// Create our data table out of JSON data loaded from server.;
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Datum');
		data.addColumn('number', 'Temperature °C');
		
		//var foo = jsonData;
		data.addRows(JSON.parse(jsonData));
		
		var options = 
		{
			animation: 
			{
				startup:true,
				duration: 1000,
				easing: 'out'
			},
			vAxis: 
			{
				viewWindow: {min: <?php echo $chartMin; ?>, max: <?php echo $chartMax; ?>},
				format: '#.#' // show axis values to 3 decimal places
				//gridlines: {color: '#FF0000',count: 2}
			}							
		};
		
		setTimeout(function()
		{		
			// Instantiate and draw our chart, passing in some options.
			var chart = new google.charts.Line(document.getElementById('chart_div'));		
			chart.draw(data, google.charts.Line.convertOptions(options));
			
			google.visualization.events.addListener(chart, 'ready', hideLoadScreen);
			google.visualization.events.addListener(chart, 'error', hideLoadScreen);
			
		},1e3);
	};
	
	function hideLoadScreen()
	{
		document.getElementById('loadScreen').style.display='none';
	};
</script>
	
<link rel="stylesheet" type="text/css" href="CSS/menu.css">
<link rel="stylesheet" type="text/css" href="CSS/main.css">
<link rel="stylesheet" type="text/css" href="CSS/chart.css">

<html>
  <head>
	<title>Diepvries monitoring:</title>
	<h1>Chart:</h1>
	<!-- Menu buttons: -->
	<div class="menu">
		<a id="" href='index.php'>Home.</a>
		<a href='index.php?addDevice=true'>Add new device.</a>
		<a href='index.php?rmDevice=true'>Disable device.</a>
		<a href='advancedSettings.php'>Advanced settings.</a>
	</div>
  </head>
  <body>
	<div class="chart_main">
		
		<!-- Paint chart: -->
		<div id="chart_div"></div>
		
		<!-- Chart Loadscreen: -->
		<div id="loadScreen">
			<img src="images/load.gif" alt="HTML5 Icon" >
			<h2> Loading... </h2>
		</div>
		
		<div id="chartForm">
			<!-- Create form: -->
			<form action method="get">
				<div id="deviceSelect">
					<!-- Auto query: -->
					Select device: 
					<select name="tableSelect" id="tableSelect">
						<option selected="selected"><?php echo htmlspecialchars($_GET['tableSelect']) ?></option> <!-- default setting. -->
						<?php
						foreach($data as $dat) 
						{ ?>
							<option value="<?= $dat ?>"><?= $dat ?></option> <!-- load database CONFIG table as option. -->
						<?php
						} ?>
					</select>
				</div>
				<div id="timeMinutes">
					<!-- Select amount of minutes: -->
					Select all data from last: 
					<select name="queryTimeSpan" id="queryTimeSpan">
						<option selected="selected"><?php echo htmlspecialchars($_GET['queryTimeSpan'])?></option>
						<option value="none">none</option>
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="30">30</option>
						<option value="60">60</option>
						<option value="120">120</option>
						<option value="240">240</option>
						<option value="480">480</option>
						<option value="720">720</option>
						<option value="1440">1440</option>
					</select> min.
				</div>
				<div id="timeSpan">
					<!-- Time date: -->
					From: <input name="dateFrom" type="date" value=<?php echo htmlspecialchars($_GET['dateFrom'])?> data-date-inline-picker="true" />
					</br>Until: <input name="dateTo" type="date" value=<?php echo htmlspecialchars($_GET['dateTo'])?> data-date-inline-picker="true" />
				</div>
				
				<div id="submitBtn">
					<!-- submit btn: -->
					<input type="submit" name="submit" value="DrawChart" />
				</div>
			</form>	
		</div>
	</div>
  </body>
</html>