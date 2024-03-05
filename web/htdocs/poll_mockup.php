<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Funkce pro převod unsigned na signed
function unsignedToSigned($val) {
    return ($val > 32767) ? $val - 65536 : $val;
}


//-------------------
  $inverterModeMap = array(
    "Waiting",
    "Checking",
    "Normal",
    "Off",
    "Permanent Fault",
    "Updating",
    "EPS Check",
    "EPS Mode",
    "Self Test",
    "Idle",
    "Standby"
  );


// Volání API Solax pomocí cURL
$SolaxPasswd = getenv("SolaxPasswd");
$SolaxUrl = getenv("SolaxUrl");

// Volání API AZ Router pomocí cURL
$AZRouterPowerUrl = getenv("AZRouterPowerUrl");


// Zpracování získaných dat
$solax = new StdClass();

//fetch reduced values from polled data:
$solax->SerNum =  "BFLMPSVZ01";
$solax->pv1Power =  rand(0, 9999);
$solax->pv2Power =  rand(0, 9999);
$solax->totalProduction =  rand(0, 999) / 10;
$solax->totalProductionInclBatt =  rand(0, 999)  / 10;
$solax->feedInPower =  unsignedToSigned( rand(0, 9999) );
$solax->totalGridIn =  ( rand(0, 9999) ) / 100;
$solax->totalGridOut =  ( rand(0, 9999) ) / 100;
$solax->loadHome =  unsignedToSigned( rand(0, 9999) );
$solax->batteryPower =  unsignedToSigned( rand(0, 999) );
$solax->totalChargedIn =  rand(0, 999) / 10;
$solax->totalChargedOut =  rand(0, 999) / 10;
$solax->batterySoC =  rand(0, 100);
$solax->batteryCap =  rand(0, 999) / 10;
$solax->batteryTemp =  rand(15, 40);
$solax->inverterTemp =  rand(15, 40);
$solax->inverterPower =  unsignedToSigned( rand(0, 9999) );
$sinverterMode = rand(0, 10 );
$solax->inverterMode =   $sinverterMode. ' ['. $inverterModeMap[ $sinverterMode] . ']';
$solax->llph1 = rand(0, 999);
$solax->llph2 =  rand(0, 999);
$solax->llph3 =  rand(0, 999);

//these comes from config file, i.e. .env file
$solax->SolaxString1Peak =  getenv('SolaxString1Peak');
$solax->SolaxString2Peak =  getenv('SolaxString2Peak');
$solax->SolaxmaxPower =  getenv('SolaxmaxPower');
$solax->SolaxHouseMaxLoad =  getenv('SolaxHouseMaxLoad');
$solax->SolaxDataPollInterval =  getenv('SolaxDataPollInterval');
$solax->totalPeak =  getenv('SolaxString1Peak') + getenv('SolaxString2Peak');

//thesse are calculated from other retrieved:
$solax->totalConsumption = $solax->totalGridIn + $solax->totalProductionInclBatt - $solax->totalGridOut;
$solax->totalPower = $solax->pv1Power   + $solax->pv2Power;
if ($solax->totalConsumption == 0 ){
    $solax->selfSufficiencyRate = 0;
} else {
   $solax->selfSufficiencyRate = ( $solax->totalProductionInclBatt - $solax->totalGridOut) * 100 / $solax->totalConsumption ;
}

$solax->CurrentTimeStamp = date ("Y-m-d H:i:s");

$solax->AZRPowerL1 = rand(0, 999); 
$solax->AZRPowerL2 = rand(0, 999); 
$solax->AZRPowerL3 = rand(0, 999); 
$solax->AZRPowerTotal = $solax->AZRPowerL1 + $solax->AZRPowerL2 + $solax->AZRPowerL3;

$solax->loadHomeL1 =  $solax->llph1 - $solax->AZRPowerL1;
$solax->loadHomeL2 =  $solax->llph2 - $solax->AZRPowerL2;
$solax->loadHomeL3 =  $solax->llph3 - $solax->AZRPowerL3;


$solax_json = json_encode( $solax );
$redis = new Redis();
$redis->connect('cache-redis', 6379);
$redis->set( "SOLAX-MOCKUP", $solax_json);


require_once ("poll-text.php");
$solax->formatted = generateText( $solax );
$solax_json = json_encode( $solax );
echo $solax_json ;
