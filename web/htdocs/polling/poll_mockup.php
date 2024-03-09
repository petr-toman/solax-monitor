<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');


// Funkce pro převod unsigned na signed
function unsignedToSigned($val)
{
  return($val > 32767) ? $val - 65536 : $val;

}

function pseudorand($val, $start = 999)
{
  $redis = new Redis();
  $redis->connect('cache-redis', 6379);
  $val = "PSEUDO-VALUES" . $val;

  if ($redis->exists($val)) {

    $newval = $redis->get($val);

    if (rand(0, 1) == 0) {
      $newval = $newval + $newval / rand(8, 12);
    } else {
      $newval = $newval - $newval / rand(8, 12);
    }
  } else {
    $newval = rand(0, $start);
  }
  $redis->set($val, $newval);
  return $newval;
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
$solax->SerNum = "BFLMPSVZ01";
$solax->pv1Power = pseudorand("pv1Power", 9999);
$solax->pv2Power = pseudorand("pv2Power", 9999);
$solax->totalProduction = pseudorand("totalProduction", 999) / 10;
$solax->totalProductionInclBatt = pseudorand("totalProductionInclBatt", 999) / 10;
$solax->feedInPower = pseudorand("feedInPower", 9999);
$solax->totalGridIn = (pseudorand("totalGridIn", 9999)) / 100;
$solax->totalGridOut = (pseudorand("totalGridOut", 9999)) / 100;
$solax->loadHome = pseudorand("loadHome", 500);
$solax->batteryPower = pseudorand("batteryPower", 500);
$solax->totalChargedIn = pseudorand("totalChargedIn", 999) / 10;
$solax->totalChargedOut = pseudorand("totalChargedOut", 999) / 10;
$solax->batterySoC = pseudorand("batterySoC", 100);
$solax->batteryCap = pseudorand("batteryCap", 999) / 10;
$solax->batteryTemp = pseudorand("batteryTemp", 40);
$solax->inverterTemp = pseudorand("inverterTemp", 40);
$solax->inverterPower = pseudorand("inverterPower", 9999);
$sinverterMode = (int) pseudorand("sinverterMode", 10);
$solax->inverterMode = $sinverterMode . ' [' . $inverterModeMap[$sinverterMode] . ']';
$solax->llph1 = pseudorand("llph1", 999);
$solax->llph2 = pseudorand("llph2", 999);
$solax->llph3 = pseudorand("llph3", 999);

//these comes from config file, i.e. .env file
$solax->SolaxString1Peak = getenv('SolaxString1Peak');
$solax->SolaxString2Peak = getenv('SolaxString2Peak');
$solax->SolaxmaxPower = getenv('SolaxmaxPower');
$solax->SolaxHouseMaxLoad = getenv('SolaxHouseMaxLoad');
$solax->SolaxDataPollInterval = getenv('SolaxDataPollInterval');
$solax->totalPeak = getenv('SolaxString1Peak') + getenv('SolaxString2Peak');

//thesse are calculated from other retrieved:
$solax->totalConsumption = $solax->totalGridIn + $solax->totalProductionInclBatt - $solax->totalGridOut;
$solax->totalPower = $solax->pv1Power + $solax->pv2Power;
if ($solax->totalConsumption == 0) {
  $solax->selfSufficiencyRate = 0;
} else {
  $solax->selfSufficiencyRate = ($solax->totalProductionInclBatt - $solax->totalGridOut) * 100 / $solax->totalConsumption;
}

$solax->CurrentTimeStamp = date("Y-m-d H:i:s");

$solax->AZRPowerL1 = pseudorand("AZRPowerL1", 999);
$solax->AZRPowerL2 = pseudorand("AZRPowerL2", 999);
$solax->AZRPowerL3 = pseudorand("AZRPowerL3", 999);
$solax->AZRPowerTotal = $solax->AZRPowerL1 + $solax->AZRPowerL2 + $solax->AZRPowerL3;

$solax->loadHomeL1 = $solax->llph1 - $solax->AZRPowerL1;
$solax->loadHomeL2 = $solax->llph2 - $solax->AZRPowerL2;
$solax->loadHomeL3 = $solax->llph3 - $solax->AZRPowerL3;


$redis = new Redis();
$redis->connect('cache-redis', 6379);
$solax_json = json_encode($solax);
$redis->set("SOLAX-MOCKUP", $solax_json);

require_once("poll-text.php");
$solax->formatted = generateText($solax);
$solax_json = json_encode($solax);
echo $solax_json;
