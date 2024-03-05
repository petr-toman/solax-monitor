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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $SolaxUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    'optType' => 'ReadRealTimeData',
    'pwd' => $SolaxPasswd
)));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$solaxResult = curl_exec($ch);
curl_close($ch);
$solaxData = json_decode($solaxResult);

// Volání API AZ Router pomocí cURL
$AZRouterPowerUrl = getenv("AZRouterPowerUrl");

$ch= curl_init();
curl_setopt($ch, CURLOPT_URL, $AZRouterPowerUrl );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$AZresult = curl_exec($ch);
curl_close($ch);
$AZRData = json_decode( $AZresult );


// Zpracování získaných dat
$solax = new StdClass();

//fetch reduced values from polled data:
$solax->SerNum =  $solaxData->sn;
$solax->pv1Power =  $solaxData->Data[14];
$solax->pv2Power =  $solaxData->Data[15];
$solax->totalProduction =  $solaxData->Data[82] / 10;
$solax->totalProductionInclBatt =  $solaxData->Data[70] / 10;
$solax->feedInPower =  unsignedToSigned( $solaxData->Data[34] );
$solax->totalGridIn =  ( $solaxData->Data[93] * 65536 + $solaxData->Data[92]) / 100;
$solax->totalGridOut =  ( $solaxData->Data[91] * 65536 + $solaxData->Data[90]) / 100;
$solax->loadHome =  unsignedToSigned( $solaxData->Data[47] );
$solax->batteryPower =  unsignedToSigned( $solaxData->Data[41] );
$solax->totalChargedIn =  $solaxData->Data[79] / 10;
$solax->totalChargedOut =  $solaxData->Data[78] / 10;
$solax->batterySoC =  $solaxData->Data[103];
$solax->batteryCap =  $solaxData->Data[106] / 10;
$solax->batteryTemp =  $solaxData->Data[105];
$solax->inverterTemp =  $solaxData->Data[54];
$solax->inverterPower =  unsignedToSigned( $solaxData->Data[9] );
$solax->inverterMode =   $solaxData->Data[19] . ' ['. $inverterModeMap[ $solaxData->Data[19] ] . ']';
$solax->llph1 =  $solaxData->Data[6];
$solax->llph2 =  $solaxData->Data[7];
$solax->llph3 =  $solaxData->Data[8];

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

$solax->AZRPowerL1 = $AZRData->input->power[0]->value; 
$solax->AZRPowerL2 = $AZRData->input->power[1]->value; 
$solax->AZRPowerL3 = $AZRData->input->power[2]->value; 
$solax->AZRPowerTotal = $solax->AZRPowerL1 + $solax->AZRPowerL2 + $solax->AZRPowerL3;

$solax->loadHomeL1 =  $solax->llph1 - $solax->AZRPowerL1;
$solax->loadHomeL2 =  $solax->llph2 - $solax->AZRPowerL2;
$solax->loadHomeL3 =  $solax->llph3 - $solax->AZRPowerL3;





$solax_json = json_encode( $solax );
$redis = new Redis();
$redis->connect('cache-redis', 6379);
$redis->set( "SOLAX-FORMATED", $solax->formatted);
$redis->set( "SOLAX", $solax_json);

echo $solax_json ;

require_once ("poll-text.php");
$solax->formatted = generateText( $solax );
