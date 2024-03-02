<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Funkce pro převod unsigned na signed
function unsignedToSigned($val) {
    return ($val > 32767) ? $val - 65536 : $val;
}

// Funkce pro vykreslení progress baru
function progress_bar($val, $max) {
    $bar_length = 20;
    $lc_progress = ($max == 0) ? 0 : ($val * $bar_length / $max);
    $progress_bar = str_repeat("#", $lc_progress) . str_repeat("_", $bar_length - $lc_progress);
    return "[$progress_bar]";
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

$divLine="------------------------------------------------\n";

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
 


//var_export($solax );
//var_export($result);
echo "<pre>";

printf( "\n" );
printf( "$divLine" );
printf ( "%s          %s \n", $solax->SerNum, date( "D, Y-m-d h:i:s T" ) );
printf( "$divLine" );
printf( "\n" );

printf ( "--- PANELY -------------------------------------\n");
printf( "        celkem: %5d W   %s\n" , $solax->totalPower,   progress_bar($solax->totalPower, $solax->totalPeak) );
printf( "      string 1: %5d W   %s\n" , $solax->pv1Power,     progress_bar($solax->pv1Power, $solax->SolaxString1Peak) );
printf( "      string 2: %5d W   %s\n" , $solax->pv2Power,     progress_bar($solax->pv1Power, $solax->SolaxString1Peak) );
printf( "dnes výroba DC: %5.1f kWh\n",  $solax->totalProduction );
echo "\n";

printf ( "--- BATERIE ------------------------------------\n");
printf( "                          %3d %%       %5d °C\n",  $solax->batterySoC, $solax->batteryTemp  );
printf( "        nabití: %5.1f kWh %s\n",  $solax->batteryCap , progress_bar($solax->batterySoC, 100));
if (  $solax->batteryPower >= 0){
printf ("      nabíjení: %5d W\n", $solax->batteryPower) ;
} else {
printf ("      vybíjení: %5d W\n", $solax->batteryPower) ;    
}
printf ("   dnes nabito: %5.1f kWh\n", $solax->totalChargedIn) ;    
printf ("        vybito: %5.1f kWh\n", $solax->totalChargedOut) ;   
echo "\n"; 

printf ( "--- STŘÍDAČ [ %'--35s\n", $solax->inverterMode."]");
printf ( "                                       %5d °C\n", $solax->inverterTemp);
printf ("         výkon: %5d kWh %s\n", $solax->inverterPower, progress_bar($solax->inverterPower, $solax->SolaxmaxPower) ) ;   
printf ("            L1: %5d W\n", $solax->llph1) ;   
printf ("            L2: %5d W\n", $solax->llph2) ;  
printf ("            L3: %5d W\n", $solax->llph3) ;  
printf ("dnes výroba AC: %5.1f kWh\n", $solax->totalProductionInclBatt) ;  
echo "\n"; 

printf ( "--- DISTRIBUČNÍ SÍŤ ----------------------------\n");
if ($solax->feedInPower < 0 ) {
printf ("         odběr: %5d W\n", $solax->feedInPower) ;  
} else {
printf ("       dodávka: %5d W\n", $solax->feedInPower) ;          
}
if ($solax->AZRPowerL1 < 0  ) {
printf (" (AZ) L1 odběr: %5d W\n", $solax->AZRPowerL1) ;  
} else {
printf ("(AZ)L1 dodávka: %5d W\n", $solax->AZRPowerL1) ;          
}
if ($solax->AZRPowerL2 < 0  ) {
printf (" (AZ) L2 odběr: %5d W\n", $solax->AZRPowerL2) ;  
} else {
printf ("(AZ)L2 dodávka: %5d W\n", $solax->AZRPowerL2) ;          
}
if ($solax->AZRPowerL3 < 0  ) {
printf (" (AZ) L3 odběr: %5d W\n", $solax->AZRPowerL3) ;  
} else {
printf ("(AZ)L3 dodávka: %5d W\n", $solax->AZRPowerL3) ;          
}
printf (" (AZ) L1+L2+L3: %5d W\n", $solax->AZRPowerTotal) ;   
printf (" dnes odebráno: %5.2f kWh\n", $solax->totalGridIn) ;   
printf ("        dodáno: %5.2f kWh\n", $solax->totalGridOut) ;   
echo "\n"; 

printf ("--- DOMÁCNOST ----------------------------------\n");
printf ("aktuální odběr: %5d W\n", $solax->loadHome) ;   
printf (" dnes spotřeba: %5.2f kWh\n", $solax->totalConsumption) ;   
printf ("  soběstačnost:   %3d %%  %s\n", $solax->selfSufficiencyRate, progress_bar($solax->selfSufficiencyRate, 100)) ;