<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

header('Content-Type: application/json; charset=utf-8');

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




$SlxDataSet = json_decode( file_get_contents( "format.json" , true ) );
$SlxDataSet->CurrentTimeStamp = date ("Y-m-d H:i:s");

$SlxDataSet->EnvConstants->SolaxUrl =  getenv('SolaxUrl');
$SlxDataSet->EnvConstants->SolaxRegNr =  getenv('SolaxRegNr');
$SlxDataSet->EnvConstants->SolaxString1Peak =  getenv('SolaxString1Peak');
$SlxDataSet->EnvConstants->SolaxString2Peak =  getenv('SolaxString2Peak');
$SlxDataSet->EnvConstants->SolaxTotalPeak = getenv('SolaxString1Peak') + getenv('SolaxString2Peak');
$SlxDataSet->EnvConstants->SolaxInverterMaxPower =  getenv('SolaxMaxPower');
$SlxDataSet->EnvConstants->SolaxHouseMaxLoad =  getenv('SolaxHouseMaxLoad');
$SlxDataSet->EnvConstants->SolaxDataPollInterval =  getenv('SolaxDataPollInterval');

require_once ("./poll-curl.php");
$solaxData = pollSolaxData();
$AZrData = pollAZrouterData();

echo ( `<pre>`.json_encode( $SlxDataSet,  JSON_PRETTY_PRINT ) );
exit();










// uložení získaných dat do logu


// Zpracování získaných dat

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
$redis->connect('redis-cache', 6379);
$redis->set( "SOLAX", $solax_json);

//echo $solax_json ;

require_once ("poll-text.php");
$solax->formatted = generateText( $solax );
$redis->set( "SOLAX-FORMATED", $solax->formatted);

echo $solax->formatted ;
