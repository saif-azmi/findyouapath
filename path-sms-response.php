<?php

/*  @author: Saif Azmi
    @purpose: hackference Hackathon
*/

include('Services/Twilio.php');

function getPathData() {
    /* Read the contents of the 'Body' field of the Request. */
    $body = $_REQUEST["Body"];
    
    /* Remove formatting from $body until it is just lowercase 
    characters without punctuation or spaces. */
    $body = preg_replace("/[^A-Za-z0-9]/u", " ", $body);
    $body = trim($body);
    $pathquery = strtolower($body);
    
    /* Spliting the query in FROM and TO */
    $pathquery = explode(" to ", $pathquery);
    /* Creating a JSON object of path data */
    $pathdata = array(
        "from" => $pathquery[0],
        "to" => $pathquery[1]
    );
    $pathdata = json_encode(array("findpath" => $pathdata),JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
    
    return $pathdata;
}

function googleMagic($origin, $destination, $mode) {
    /* Google Directions API */
    $apiCallURL = "https://maps.googleapis.com/maps/api/directions/json?";
    $apiCallURL .= "origin=".urlencode($origin);
    $apiCallURL .= "&destination=".urlencode($destination);
    //$apiCallURL .= "&mode=".urlencode($mode);
    $apiCallURL .= "&mode=walking";
    $apiCallURL .= "&region=uk";
    $apiCallURL .= "&key={your_key}";
    
    return file_get_contents($apiCallURL);
}

function printPathMsg($pathJSON) {

	//@TODO: try to build a string of the whole msg.
	//@TODO: take in the mode of travel use the Kyword BY maybe.
	//@TODO: format the string
	//@TODO: determine the string length and figure out to send the whole msg.
    $pathJSON = json_decode($pathJSON, true);
    $routes = $pathJSON["routes"];
    $legs = $routes[0]["legs"];
    
    $distance = $legs[0]["distance"]["text"];
    $duration = $legs[0]["duration"]["text"];
    
    echo "Distance: $distance";
    echo "\nTime: $duration";
    
    /* Parse the steps */
    $steps = $legs[0]["steps"];
    
    for($i = 0; $i < count($steps); $i++) {
        
        $step = $steps[$i]["html_instructions"];
        $step = strip_tags($step);
        if (strpos($step, "Destination") !== false) {
            $step = str_replace("Destination","\n\nDestination",$step);
        }
        echo "\n$step\n";
    }
}

function findYouAPath() {
    /* Get the path data */
    $findyouapath = getPathData();
    $findyouapath = json_decode($findyouapath, true);
    $findyouapath = $findyouapath["findpath"];
    
    $origin = $findyouapath["from"];
    $destination = $findyouapath["to"];
    
    //echo urlencode($origin);
    //echo urlencode($destination);
    printPathMsg(googleMagic($origin, $destination));
}

header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
?>

<Response>
    <Message>
        <?php
            findYouAPath();
        ?>
    </Message>
</Response>
