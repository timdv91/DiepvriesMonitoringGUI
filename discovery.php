<?php

	function discoverDevices()
	{
		$ip = "255.255.255.255";
		$port = 30303;
		$str = "D";

		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
		socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
		socket_sendto($sock, $str, strlen($str), 0, $ip, $port);

		$data;
		$I=0;
		
		//fill array with some data to prevent error if no discovery answer is given:
		$data[0][0] = "NULL";
		$data[0][1] = "NULL";
		
		while(true) 
		{
		  $ret = @socket_recvfrom($sock, $buf, 36, 0, $ip, $port);
		  if($ret == false) 
			  break;
		  
		  $pieces = explode("\n", $buf);
		  $pieces[1] = substr($pieces[1], 0, -1);
		  
		  $data[$I][0] = $pieces[1];
		  $data[$I][1] = $ip;
		  
		  $I++;
		}

		socket_close($sock);
		return $data;
	}

?>