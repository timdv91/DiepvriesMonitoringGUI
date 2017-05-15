<!-- This file contains all objects shown in the smaal addDevice box. -->
<?php
	//Include the deviceLib library, in this library all mysql connections are managed:
	include '/db-connect/devicesLib.php';
	include 'discovery.php';
	
	//Send discovery to find all unset devices in the network:
	$discoveryData = @discoverDevices();
	$MacAdresses = @getMacFromDatabase();
	
	$newDeviceMACArr = array();
	
	$noDeviceIsSet = false;
	if(count($MacAdresses) <= 0)
		$noDeviceIsSet = true;
	
	foreach($discoveryData as $DMACIP)
	{	
		if($noDeviceIsSet == true)
		{
			array_push($newDeviceMACArr, $DMACIP[0]);
		}			
		else if(!in_array($DMACIP[0],$MacAdresses))
		{
			array_push($newDeviceMACArr, $DMACIP[0]);
		}
	}
	//Load all devices currently set in database, we can reuse the 'getDropDownData' function for this.
	$data = @getDropDownData(); 
	
	if(isset($_GET['NAME']) || isset($_GET['IP']) || isset($_GET['PORT']) || isset($_GET['TEMPLOW']) || isset($_GET['TEMPHIGH'])) //if some values are set, this is not the first page load:
	{
		if($_GET['NAME'] != "?" && $_GET['deviceSelect'] != "Select device" && $_GET['deviceSelect'] != "No devices available" && $_GET['PORT'] != "?" && $_GET['TEMPLOW'] != null && $_GET['TEMPHIGH'] != null) //Check if textboxes are changed in value:
		{
			$IP = "0.0.0.0"; //this is send to db when errors occurs.
			
			//Get IP from discoveryData:
			foreach($discoveryData as $DMACIP)
			{
				if (in_array($_GET['deviceSelect'], $DMACIP)) 
				{
					$IP = $DMACIP[1];
				}
			}
			
			$state = @addDevice($_GET['NAME'],$IP,$_GET['PORT'],$_GET['deviceSelect'],$_GET['TEMPLOW'],$_GET['TEMPHIGH']); //add device to database table CONFIG; returns true when successfull;
			
			if ($state == "false") //device already exists;
			{
				$message = "Warning: This device name is already in use! Choose a unique device name.";
				echo "<script type='text/javascript'>alert('$message');</script>";
			}
			else if($state == "true") //device addes successfully;
			{
				$message = "Device added successfully!";
				echo "
				<script type='text/javascript'>
					alert('$message');
					sessionStorage.setItem('pageNeedsReload', 'true'); // Save data to sessionStorage
				</script>";
				//	above in the small chunck of javascript code, we use sessionStorage to save variable globaly. 
				//	This is necessary for automated page reload after adding a new device.
			}
		}
		else //if input value is not changed:
		{
			$message = "Warning: Some of your inputs are wrong!";
			echo "<script type='text/javascript'>alert('$message');</script>";
		}
	}else{ //on first page load, fill textboxes with some chars:
		$_GET['NAME'] = '?';
		$_GET['IP'] = '?';
		$_GET['PORT'] = '23';
		$_GET['TEMPLOW'] = '-100';
		$_GET['TEMPHIGH'] = '-50';
	}
?>

<!-- add subMenu stylesheet for nice layout: -->
<link rel="stylesheet" type="text/css" href="CSS/subMenu.css">

<!-- this div class contains the form within the addDevice box: -->
<div class="subMenu">
	<h2>Add device:</h2>	
	
	<form class="CfgForm" action method="get">
		Name: <input type="text" name="NAME" value=<?php echo htmlspecialchars($_GET['NAME'])?>   /> </br> 
		
		MAC: <select name="deviceSelect" id="tableSelect">
					<?php 
						if(count($newDeviceMACArr) <= 0)
							echo "<option selected=\"selected\">No devices available</option>";
						else
							echo "<option selected=\"selected\">Select device</option>";
					?>
					<?php
					foreach($newDeviceMACArr as $dat) //use the data loaded from getDropDownData function call to fill the combobox
					{ ?>
						<option value="<?= $dat ?>"><?= $dat ?></option> <!-- load database CONFIG table as option. -->
					<?php
					} ?>
				</select>
		</br>
		PORT: <input type="text" name="PORT" value=<?php echo htmlspecialchars($_GET['PORT'])?> /> </br>
		Range: <input type="text" name="TEMPLOW" value=<?php echo htmlspecialchars($_GET['TEMPLOW'])?> /> </br>
		 <input type="text" name="TEMPHIGH" value=<?php echo htmlspecialchars($_GET['TEMPHIGH'])?> /> </br>
		<!-- submit btn: -->
		<input id="addSave" type="submit" name="submit" value="Save" />
	</form>

</div>