<!-- This file contains all objects shown inside the remove device box: -->
<?php
	//Include the deviceLib library, in this library all mysql connections are managed:
	include '/db-connect/devicesLib.php';
	
	//Load all devices currently set in database, we can use the 'getDropDownData' function for this;
	$data = @getDropDownData(); //$data is used to fill the combobox with all existing devices;
	
	// tableSelect is set after submit: 
	if(isset($_GET['tableSelect']))
	{
		$state = deleteDevice($_GET['tableSelect']); //use deleteDevice function to delete an existing device.
		if($state == "true") //if this returns true, the remove was successfully executed;
		{
			$message = "Device removed successfully!";
			echo "
				<script type='text/javascript'>
					alert('$message');
					sessionStorage.setItem('pageNeedsReload', 'true'); // Save data to sessionStorage
				</script>";
				//	above in the small chunck of javascript code, we use sessionStorage to save variable globaly. 
				//	This is necessary for automated page reload after adding a new device.
		}
		else //When deleting existing device returns false, an unknown error is shown. --> this should be impossible and is never shown;
		{
			$message = "
			Could not remove this device for an unknown reason :-( . 
			Contact webprogrammer 'TimDV' to fix this bug! 
			Write down the next error code: [ ERROR LINE: 23, FILE: rmDevice.php ] !";
			
			echo "<script type='text/javascript'>alert('$message');</script>"; //show this much to long error text on the screen.
		}
	}
?>

<!-- add subMenu stylesheet for nice layout: -->
<link rel="stylesheet" type="text/css" href="CSS/subMenu.css">

<!-- this div class contains the form within the rmDevice box: -->
<div class="subMenu">
	<h2>Remove device:</h2>	
	<form class="CfgForm" action method="get">
		Select device:
		<select name="tableSelect" id="tableSelect">
			<option selected="selected">Select device</option>
			<?php
			foreach($data as $dat) //use the data loaded from getDropDownData function call to fill the combobox
			{ ?>
				<option value="<?= $dat ?>"><?= $dat ?></option> <!-- load database CONFIG table as option. -->
			<?php
			} ?>
		</select>
		
		<!-- submit btn: -->
		<input id="rmSave" type="submit" name="submit" value="Delete" />
	</form>
	
</div>