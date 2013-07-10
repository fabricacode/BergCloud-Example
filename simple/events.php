<?php
    // RETURN A JSON WITH THE PRODUCT EVENTS STORED IN THE DATABASE
    // RESULTS CAN BE FILTERED BY DEVICE ADDRESS/EVENTNAME/PIN/PIN TYPE (ANALOG-DIGITAL)
    // EXAMPLE: ..../berg/simple/events.php?event=YOUREVENT&address=YOURDEVICEADDRESS&num=12 
     try
  {
    $database = new SQLiteDatabase('eventsDb.sqlite', 0666, $error);
  }
  catch(Exception $e)
  {
    die($error);
  }
  
	$event=$_GET["event"];
  	$address=$_GET["address"];
  	$num=$_GET["num"];
  	
  	
  	if(!is_numeric($num ))
  		$num=10;
  	$query = "SELECT * FROM Events "; 
 
   	$gotevents=false;
   	if(!empty($event)) {
  		 $query = $query." WHERE eventname = '".$event."' ";
  	 	$gotevents=true;
   	}
   	if(!empty($address)) {
  		if(!$gotevents){
  			$query = $query." WHERE address = '".$address."' ";
  		}
  		else{
  			$query = $query." AND address = '".$address."' ";
  		}
  		$gotevents=true;
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
    					'payload' => base64_decode($r["payload"]), 
    					'gmtime' => $r["time"]);
    	}
		print json_encode($rows);
    }
  ?>

