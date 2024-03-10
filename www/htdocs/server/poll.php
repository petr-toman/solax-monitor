<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

header('Content-Type: application/json; charset=utf-8');

require_once ("./poll-curl.php");
$solaxData = pollSolaxData();
$AZRData   = pollAZrouterData();

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

//----

$SlxDataSet->Panel->pv1Power->value =  $solaxData->Data[14];
$SlxDataSet->Panel->pv2Power->value =  $solaxData->Data[15];
$SlxDataSet->Panel->totalPower->value =  $solaxData->Data[14] + $solaxData->Data[15];
$SlxDataSet->Panel->dailyProduction->value =  $solaxData->Data[82] / 10;

//----

$SlxDataSet->Battery->batterySoC->value  =  $solaxData->Data[103];
$SlxDataSet->Battery->batteryTemp->value =  $solaxData->Data[105];
$SlxDataSet->Battery->chargeCap->value =  $solaxData->Data[106] / 10;
$SlxDataSet->Battery->batteryPower->value =  unsignedToSigned( $solaxData->Data[41] );
$SlxDataSet->Battery->totalChargedIn->value =  $solaxData->Data[79] / 10;
$SlxDataSet->Battery->totalChargedOut->value =  $solaxData->Data[78] / 10;


//----
$SlxDataSet->inverter->inverterMode->value   = $solaxData->Data[19] . ' ['. $inverterModeMap[ $solaxData->Data[19] ] . ']';
$SlxDataSet->inverter->inverterTemp->value   = $solaxData->Data[54];
$SlxDataSet->inverter->inverterPower->value  =  unsignedToSigned( $solaxData->Data[9] );
$SlxDataSet->inverter->powerL1->value  =  $solaxData->Data[6];
$SlxDataSet->inverter->powerL2->value  =  $solaxData->Data[7];
$SlxDataSet->inverter->powerL3->value  =  $solaxData->Data[8];
$SlxDataSet->inverter->totalProductionInclBatt->value  =  $solaxData->Data[70] / 10;

//----
$SlxDataSet->Grid->feedInPower->value  =  unsignedToSigned( $solaxData->Data[34] );
$SlxDataSet->Grid->AZRPowerL1->value  = $AZRData->input->power[0]->value; 
$SlxDataSet->Grid->AZRPowerL2->value  = $AZRData->input->power[1]->value; 
$SlxDataSet->Grid->AZRPowerL3->value  = $AZRData->input->power[2]->value; 
$SlxDataSet->Grid->AZRPowerTotal->value  = $AZRData->input->power[0]->value + $AZRData->input->power[1]->value +  $AZRData->input->power[2]->value; 
$SlxDataSet->Grid->totalGridIn->value   =  ( $solaxData->Data[93] * 65536 + $solaxData->Data[92]) / 100;
$SlxDataSet->Grid->totalGridOut->value   =  ( $solaxData->Data[91] * 65536 + $solaxData->Data[90]) / 100;
//----

$SlxDataSet->Home->loadHome->value   = unsignedToSigned( $solaxData->Data[47] );
$SlxDataSet->Home->loadHomeL1->value   = $SlxDataSet->inverter->powerL1->value -$SlxDataSet->Grid->AZRPowerL1->value ;
$SlxDataSet->Home->loadHomeL2->value   = $SlxDataSet->inverter->powerL2->value -$SlxDataSet->Grid->AZRPowerL2->value ;
$SlxDataSet->Home->loadHomeL3->value   = $SlxDataSet->inverter->powerL3->value -$SlxDataSet->Grid->AZRPowerL3->value ;
$SlxDataSet->Home->totalConsumption->value   = $SlxDataSet->Grid->totalGridIn->value + $SlxDataSet->inverter->totalProductionInclBatt->value - $SlxDataSet->Grid->totalGridOut->value ;
if ($SlxDataSet->Home->totalConsumption->value == 0 ){
  $SlxDataSet->Home->selfSufficiencyRate->value   = 0;
} else {
  $SlxDataSet->Home->selfSufficiencyRate->value  = ( $SlxDataSet->inverter->totalProductionInclBatt->value - $SlxDataSet->Grid->totalGridOut->value ) * 100 / $SlxDataSet->Home->totalConsumption->value  ;
}

//----
$solax_json = json_encode( $SlxDataSet,  JSON_PRETTY_PRINT );
echo ($solax_json );

//uložíme to do redisu, ať se ostatní mohou koukat bez nutnosti to znovu tvořit.
$redis = new Redis();
$redis->connect('cache-redis', 6379);
$redis->set( "SOLAX", $solax_json);
exit(  );

//--------------//
require_once ("poll-text.php");
$solax->formatted = generateText( $solax );
$redis->set( "SOLAX-FORMATED", $solax->formatted);