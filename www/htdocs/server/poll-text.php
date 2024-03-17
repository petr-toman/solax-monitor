<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');


$redis = new Redis();
$redis->connect('cache-redis', 6379);
$solax_json = $redis->get( "SOLAX" );

if ( $solax_json ) { 
    //echo $solax_json;
    $SlxDataSet = json_decode($solax_json);
    //var_dump( $SlxDataSet );
} else {    
    require_once dirname(__FILE__) . '/poll-curl.php';
    $SlxDataSet = convertSolax();
    $redis->connect('cache-redis', 6379);
    $redis->set( "SOLAX", json_encode( $SlxDataSet,  JSON_PRETTY_PRINT ));
    $redis->expire('SOLAX', 5);
   // $redis->expire('SOLAX', 5);
}
//var_dump( $SlxDataSet );

echo (generateText( $SlxDataSet  ));

$redis->set( "SOLAX-FORMATED",$SlxDataSet );
$redis->expire('SOLAX-FORMATED', 5);


// Funkce pro vykreslení progress baru
function progress_bar($val, $max) {
    $bar_length = 20;
    $lc_progress = ($max == 0) ? 0 : (int) ($val * $bar_length / $max);
    if ((  $bar_length - $lc_progress ) < 0 || $lc_progress < 0  ){  //out of bounds!
        $progress_bar = str_repeat("!", $bar_length );
    } else {
        $progress_bar = str_repeat("#", $lc_progress) . str_repeat("_", $bar_length - $lc_progress);
    }
    return "[$progress_bar]";
}

// Funkce pro generování plain text přehledu
function generateText( $solax ){

    $formatted = "";
    $formatted .= sprintf( "<pre>" );

     $formatted .= sprintf( "------------------------------------------------\n" );
     $formatted .= sprintf( "%s              %s \n", $solax->EnvConstants->SolaxRegNr, $solax->CurrentTimeStamp );
     $formatted .= sprintf( "------------------------------------------------\n" );
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- PANELY -------------------------------------\n");
     $formatted .= sprintf( "        celkem: %5d W   %s\n" , $solax->Panel->totalPower->value,   progress_bar($solax->Panel->totalPower->value, $solax->EnvConstants->SolaxTotalPeak ));
     $formatted .= sprintf( "      string 1: %5d W   %s\n" , $solax->Panel->pv1Power->value,     progress_bar($solax->Panel->pv1Power->value, $solax->EnvConstants->SolaxString1Peak ) );
     $formatted .= sprintf( "      string 2: %5d W   %s\n" , $solax->Panel->pv2Power->value,     progress_bar($solax->Panel->pv2Power->value, $solax->EnvConstants->SolaxString2Peak) );
     $formatted .= sprintf( "dnes výroba DC: %5.1f kWh\n",  $solax->Panel->dailyProduction->value );
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- BATERIE ------------------------------------\n");
     $formatted .= sprintf( "                          %3d %%       %5d °C\n",  $solax->Battery->batterySoC->value, $solax->Battery->batteryTemp->value  );
     $formatted .= sprintf( "        nabití: %5.1f kWh %s\n",  $solax->Battery->chargeCap->value , progress_bar($solax->Battery->batterySoC->value, 100));
    if (  $solax->Battery->batteryPower->value >= 0){
     $formatted .= sprintf("      nabíjení: %5d W\n", $solax->Battery->batteryPower->value) ;
    } else {
     $formatted .= sprintf("      vybíjení: %5d W\n", $solax->Battery->batteryPower->value) ;    
    }
     $formatted .= sprintf("   dnes nabito: %5.1f kWh\n", $solax->Battery->totalChargedIn->value) ;    
     $formatted .= sprintf("        vybito: %5.1f kWh\n", $solax->Battery->totalChargedOut->value) ;   
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- STŘÍDAČ [ %'--34s\n", $solax->inverter->inverterMode->value." ]");
     $formatted .= sprintf( "                                       %5d °C\n", $solax->inverter->inverterTemp->value);
     $formatted .= sprintf("         výkon: %5d kWh %s\n", $solax->inverter->inverterPower->value, progress_bar($solax->inverter->inverterPower->value, $solax->EnvConstants->SolaxInverterMaxPower ) ) ;   
     $formatted .= sprintf("            L1: %5d W\n", $solax->inverter->powerL1->value ) ;   
     $formatted .= sprintf("            L2: %5d W\n", $solax->inverter->powerL2->value ) ;  
     $formatted .= sprintf("            L3: %5d W\n", $solax->inverter->powerL3->value ) ;  
     $formatted .= sprintf("dnes výroba AC: %5.1f kWh\n", $solax->inverter->totalProductionInclBatt->value ) ;  
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- DISTRIBUČNÍ SÍŤ ----------------------------\n");
    if ($solax->Grid->feedInPower->value  < 0 ) {
     $formatted .= sprintf("         odběr: %5d W\n", $solax->Grid->feedInPower->value ) ;  
    } else {
     $formatted .= sprintf("       dodávka: %5d W\n", $solax->Grid->feedInPower->value ) ;          
    }
    if ($solax->Grid->AZRPowerL1->value  < 0  ) {
     $formatted .= sprintf(" (AZ) L1 odběr: %5d W\n", $solax->Grid->AZRPowerL1->value ) ;  
    } else {
     $formatted .= sprintf("(AZ)L1 dodávka: %5d W\n", $solax->Grid->AZRPowerL1->value  );          
    }
    if ($solax->Grid->AZRPowerL2->value  < 0  ) {
     $formatted .= sprintf(" (AZ) L2 odběr: %5d W\n", $solax->Grid->AZRPowerL2->value ) ;  
    } else {
     $formatted .= sprintf("(AZ)L2 dodávka: %5d W\n", $solax->Grid->AZRPowerL2->value ) ;          
    }
    if ($solax->Grid->AZRPowerL3->value  < 0  ) {
     $formatted .= sprintf(" (AZ) L3 odběr: %5d W\n", $solax->Grid->AZRPowerL3->value ) ;  
    } else {
     $formatted .= sprintf("(AZ)L3 dodávka: %5d W\n", $solax->Grid->AZRPowerL3->value ) ;          
    }
     $formatted .= sprintf(" (AZ) L1+L2+L3: %5d W\n", $solax->Grid->AZRPowerTotal->value) ;   
     $formatted .= sprintf(" dnes odebráno: %5.2f kWh\n", $solax->Grid->totalGridIn->value  );   
     $formatted .= sprintf("        dodáno: %5.2f kWh\n", $solax->Grid->totalGridOut->value ) ;   
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf("--- DOMÁCNOST ----------------------------------\n");
     $formatted .= sprintf("aktuální odběr: %5d W\n", $solax->Home->loadHome->value ) ;   
     $formatted .= sprintf("      odběr L1: %5d W\n", $solax->Home->loadHomeL1->value ) ; 
     $formatted .= sprintf("      odběr L2: %5d W\n", $solax->Home->loadHomeL2->value ) ;   
     $formatted .= sprintf("      odběr L3: %5d W\n", $solax->Home->loadHomeL3->value ) ;     
     $formatted .= sprintf(" dnes spotřeba: %5.2f kWh\n", $solax->Home->totalConsumption->value ) ;  
     $formatted .= sprintf("  soběstačnost:   %3d %%  %s\n", $solax->Home->selfSufficiencyRate->value , progress_bar($solax->Home->selfSufficiencyRate->value , 100)) ;

     $formatted .= sprintf( "</pre>" );


    return $formatted ;

}