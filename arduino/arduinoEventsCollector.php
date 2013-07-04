<?php
	//STORE THE BERGCLOUD PRODUCT EVENTS IN A SQLITE DATABASE.
	//IT ALSO PARSE THE PAYLOAD MESSAGE SENT BY THE SKETCH BergCloudArduino.ino FOR GETTING THE ARDUINO PIN VALUES 
	
	try{
  		//CREATE A SQLITE DATABASE TABLE, IF IT DOESN'T EXIST
  		
  		$database = new SQLiteDatabase('eventsDb.sqlite', 0666, $error);
    	$query = 'CREATE TABLE Events (address TEXT, eventname TEXT, payload TEXT, time TEXT, type TEXT, pin INTEGER, value INTEGER)';
  		$database->queryExec($query, $error);
  	}
  	catch(Exception $e){
    	die($error);
  	}
  	
  	$address =$_POST["address"];
  	$payload=$_POST["payload"];			
  	$eventName=$_GET["event"];
	$dt=gmdate('Y-m-d H:i:s');
	
	//PARSING DATA
	$data=unpack("ctype/cpin/ivalue",base64_decode($payload));
	$type=chr($data['type']);
	
    if(($type=='A' || $type=='D') && is_numeric($data['pin']) && is_numeric($data['value'])){
		$query = "INSERT INTO Events (address, eventname, payload, time, type, pin, value) VALUES ('".$address."', '".$eventName."', '".$payload."', '".$dt."', '".$type."', '".$data['pin']."', '".$data['value']."' );";
  		if(!$database->queryExec($query, $error))
    		die($error);
    }
 ?>

