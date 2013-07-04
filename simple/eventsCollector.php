<?php
	//STORE THE PRODUCT EVENTS IN A SQLITE DATABASE
  	try{
  		//CREATE A SQLITE DATABASE TABLE, IF IT DOESN'T EXIST
  		
  		$database = new SQLiteDatabase('eventsDb.sqlite', 0666, $error);
    	$query = 'CREATE TABLE Events (address TEXT, eventname TEXT, payload TEXT, time TEXT)';
  		$database->queryExec($query, $error);
  	}
  	catch(Exception $e){
    	die($error);
  	}
  	//SAVE THE EVENT DATA IN A TABLE ROW
  	$address =$_POST["address"];
  	$payload=$_POST["payload"];			
  	$eventName=$_GET["event"];
  	
	$dt=gmdate('Y-m-d H:i:s');
	$query = "INSERT INTO Events (address, eventname, payload, time) VALUES ('".$address."', '".$eventName."', '".$payload."', '".$dt."');";
  	if(!$database->queryExec($query, $error))
    	die($error);
 ?>

