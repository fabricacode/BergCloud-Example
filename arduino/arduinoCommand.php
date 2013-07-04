<?php

//SEND A BERGCLOUD COMMAND THROUGH A GET REQUEST. 
//THE PAYLOAD IS INTERPRETED BY THE ARDUINO SKETCH BergCloudArduino.ino IN ORDER TO WRITE A VALUE TO AN ARDUINO DIGITAL/ANALOG PIN
//THE REQUEST SHOULD LOOK LIKE THIS:
//..../berg/arduino/arduinoCommand.php?apitoken=MYAPITOKEN&command=MYCOMMAND&address=MYDEVICEADDRESS&value=10&pin=3&type=A


$apitoken=$_GET["apitoken"];
$command=$_GET["command"];
$address=$_GET["address"];

$type=$_GET["type"]; //"A" FOR ANALOG, "D" FOR DIGITAL
$pin=$_GET["pin"];
$value=$_GET["value"];

if(empty($payload)){
	$payload="";
	createFormattedPayload( $type, 
							$pin, 
						   $value);
}

if(strlen($payload)>0)
	sendBergcloudCommand($address,$command, $apitoken,$payload);
else
	die("Error: Parameters missing. GET Parameters needed: type, value, pin");











//CREATE A PAYLOAD FORMATTED MESSAGE FOR WRITING A VALUE TO AN ARDUINO DIGITAL/ANALOG PIN
//EXAMPLE: WRITE 1 TO THE DIGITAL PIN 2 -> PAYLOAD: D021
//EXAMPLE: WRITE 512 TO THE ANALOG PIN 2 ->PAYLOAD: A020512
function createFormattedPayload($type, $pin, $value){
	if(is_numeric($pin) && strlen($pin)<=2){
		
		//PIN AS 2 CHAR
		while(strlen($pin)!=2)
			$pin="0".$pin;
		
		//ANALOG: VALUE BETWEEN 0 AND 1024
		if($type=='A'&& $value>=0 && $value<=1023 ){
			
			//VALUE AS 4 CHAR: EXAMPLE (0001, 0123, 1024)
			while(strlen($value)!=4){
				$value="0".$value;
			}
			$payload=$type.$pin.$value;
			
		}
		
		//DIGITAL: VALUE SHOULD BE 0 OR 1
		if($type=='D' && ($value=='0' || $value=='1')){
			$payload=$type.$pin.$value;
		}
	}
}

//SEND A BERCGCLOUD COMMAND 
function sendBergcloudCommand ($address, $command, $apitoken, $payload) {
	if(empty($address) || empty($command) || empty($apitoken)){
		die("ERROR! Required GET Parameter: apitoken, command, address.");
	}
	$url = "http://remote.bergcloud.com/v1/products/".$apitoken."/".$command;
	$datatopost = array (
		"address" =>$address,
		"payload" => base64_encode($payload)
	);

	$ch = curl_init ($url);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_HEADER, TRUE);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
	$returndata = curl_exec ($ch);
}
?>