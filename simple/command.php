
<?php

//SEND A BERGCLOUD COMMAND THROUGH A GET REQUEST
//..../berg/simple/command.php?apitoken=MYAPITOKEN&command=MYCOMMAND&address=MYDEVICEADDRESS&payload=MYPAYLOAD


$apitoken=$_GET["apitoken"];
$command=$_GET["command"];
$payload=$_GET["payload"];
$address=$_GET["address"];
sendCommand($address,$command, $apitoken,$payload);



//SEND A BERCGCLOUD COMMAND 
function sendCommand ($address, $command, $apitoken,$payload) {
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