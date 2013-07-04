<?php
    // RETURN A JSON WITH THE EVENTS SENT BY THE ARDUINO SKETCH BergCloudArduino.ino AND STORED IN THE DATABASE
    // RESULTS CAN BE FILTERED BY DEVICE ADDRESS/EVENT/PIN/PIN TYPE (ANALOG-DIGITAL)
    // EXAMPLE: ..../berg/arduino/arduinoEvents.php?pin=0&type=A 
    
    
    
    try{
    	$database = new SQLiteDatabase('eventsDb.sqlite', 0666, $error);
  	}
  	catch(Exception $e){
	    die($error);
  	}
  	$event=$_GET["event"];
  	$address=$_GET["address"];
  	$num=$_GET["num"]; // Number of results
  	$pin=$_GET["pin"]; // Number of the pin
  	$type=$_GET["type"];// "A" for analog pin/ "D" for digital pin
  	
  	
  	if(!is_numeric($num ))
  		$num=10;
  	
  	$query = "SELECT * FROM Events "; 
 
   	$wroteWhereCondition=false;
   	if(!empty($event)) {
  		$query = $query." WHERE eventname = '".$event."' ";
  	 	$wroteWhereCondition=true;
   	}
   	if(!empty($address)) {
  		if(!$wroteWhereCondition){
  			$query = $query." WHERE address = '".$event."' ";
  		}
  		else{
  			$query = $query." AND address = '".$address."' ";
  		}
  		$wroteWhereCondition=true;
  	}
  	if(!empty($pin)) {
  		if(!$wroteWhereCondition){
  			$query = $query." WHERE pin = '".$pin."' ";
  		}
  		else{
  			$query = $query." AND pin = '".$pin."' ";
  		}
  		$wroteWhereCondition=true;
  	}
  	if(!empty($type)) {
  		if(!$wroteWhereCondition){
  			$query = $query." WHERE type = '".$type."' ";
  		}
  		else{
  			$query = $query." AND type = '".$type."' ";
  		}
  		$wroteWhereCondition=true;
  	}
  	$query=$query." ORDER BY time DESC";
	if($result = $database->query($query, SQLITE_BOTH, $error)){
		$numrows=sqlite_num_rows($result);
		$rows = array();
		while($r = $result->fetch()) {
			if($result->key()==$num)
				break;
		
			$rows[] = array('event' => $r["eventname"], 
    					'address' => $r["address"], 
    					'gmtime' => $r["time"],
    					'type' => $r["type"],
    					'pin' => $r['pin'],
    					'value' => $r['value']
    					);
    	}
		print json_encode($rows);
    }
  
    
    ?>

