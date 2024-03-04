<?php

// Funkce pro vykreslení progress baru
function progress_bar($val, $max) {
    $bar_length = 20;
    $lc_progress = ($max == 0) ? 0 : (int) ($val * $bar_length / $max);
    $progress_bar = str_repeat("#", $lc_progress) . str_repeat("_", $bar_length - $lc_progress);
    return "[$progress_bar]";
}

// Funkce pro generování plain text přehledu
function generateText( $solax ){

    $formatted = "";
    $formatted .= sprintf( "<pre>" );

     $formatted .= sprintf( "------------------------------------------------\n" );
     $formatted .= sprintf( "%s          %s \n", $solax->SerNum, date( "D, Y-m-d h:i:s T" ) );
     $formatted .= sprintf( "------------------------------------------------\n" );
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- PANELY -------------------------------------\n");
     $formatted .= sprintf( "        celkem: %5d W   %s\n" , $solax->totalPower,   progress_bar($solax->totalPower, $solax->totalPeak) );
     $formatted .= sprintf( "      string 1: %5d W   %s\n" , $solax->pv1Power,     progress_bar($solax->pv1Power, $solax->SolaxString1Peak) );
     $formatted .= sprintf( "      string 2: %5d W   %s\n" , $solax->pv2Power,     progress_bar($solax->pv1Power, $solax->SolaxString1Peak) );
     $formatted .= sprintf( "dnes výroba DC: %5.1f kWh\n",  $solax->totalProduction );
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- BATERIE ------------------------------------\n");
     $formatted .= sprintf( "                          %3d %%       %5d °C\n",  $solax->batterySoC, $solax->batteryTemp  );
     $formatted .= sprintf( "        nabití: %5.1f kWh %s\n",  $solax->batteryCap , progress_bar($solax->batterySoC, 100));
    if (  $solax->batteryPower >= 0){
     $formatted .= sprintf("      nabíjení: %5d W\n", $solax->batteryPower) ;
    } else {
     $formatted .= sprintf("      vybíjení: %5d W\n", $solax->batteryPower) ;    
    }
     $formatted .= sprintf("   dnes nabito: %5.1f kWh\n", $solax->totalChargedIn) ;    
     $formatted .= sprintf("        vybito: %5.1f kWh\n", $solax->totalChargedOut) ;   
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- STŘÍDAČ [ %'--35s\n", $solax->inverterMode."]");
     $formatted .= sprintf( "                                       %5d °C\n", $solax->inverterTemp);
     $formatted .= sprintf("         výkon: %5d kWh %s\n", $solax->inverterPower, progress_bar($solax->inverterPower, $solax->SolaxmaxPower) ) ;   
     $formatted .= sprintf("            L1: %5d W\n", $solax->llph1) ;   
     $formatted .= sprintf("            L2: %5d W\n", $solax->llph2) ;  
     $formatted .= sprintf("            L3: %5d W\n", $solax->llph3) ;  
     $formatted .= sprintf("dnes výroba AC: %5.1f kWh\n", $solax->totalProductionInclBatt) ;  
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf( "--- DISTRIBUČNÍ SÍŤ ----------------------------\n");
    if ($solax->feedInPower < 0 ) {
     $formatted .= sprintf("         odběr: %5d W\n", $solax->feedInPower) ;  
    } else {
     $formatted .= sprintf("       dodávka: %5d W\n", $solax->feedInPower) ;          
    }
    if ($solax->AZRPowerL1 < 0  ) {
     $formatted .= sprintf(" (AZ) L1 odběr: %5d W\n", $solax->AZRPowerL1) ;  
    } else {
     $formatted .= sprintf("(AZ)L1 dodávka: %5d W\n", $solax->AZRPowerL1) ;          
    }
    if ($solax->AZRPowerL2 < 0  ) {
     $formatted .= sprintf(" (AZ) L2 odběr: %5d W\n", $solax->AZRPowerL2) ;  
    } else {
     $formatted .= sprintf("(AZ)L2 dodávka: %5d W\n", $solax->AZRPowerL2) ;          
    }
    if ($solax->AZRPowerL3 < 0  ) {
     $formatted .= sprintf(" (AZ) L3 odběr: %5d W\n", $solax->AZRPowerL3) ;  
    } else {
     $formatted .= sprintf("(AZ)L3 dodávka: %5d W\n", $solax->AZRPowerL3) ;          
    }
     $formatted .= sprintf(" (AZ) L1+L2+L3: %5d W\n", $solax->AZRPowerTotal) ;   
     $formatted .= sprintf(" dnes odebráno: %5.2f kWh\n", $solax->totalGridIn) ;   
     $formatted .= sprintf("        dodáno: %5.2f kWh\n", $solax->totalGridOut) ;   
     $formatted .= sprintf( "\n" );
    
     $formatted .= sprintf("--- DOMÁCNOST ----------------------------------\n");
     $formatted .= sprintf("aktuální odběr: %5d W\n", $solax->loadHome) ;   
     $formatted .= sprintf("      odběr L1: %5d W\n", $solax->loadHomeL1) ; 
     $formatted .= sprintf("      odběr L2: %5d W\n", $solax->loadHomeL2) ;   
     $formatted .= sprintf("      odběr L3: %5d W\n", $solax->loadHomeL3) ;     
     $formatted .= sprintf(" dnes spotřeba: %5.2f kWh\n", $solax->totalConsumption) ;  
     $formatted .= sprintf("  soběstačnost:   %3d %%  %s\n", $solax->selfSufficiencyRate, progress_bar($solax->selfSufficiencyRate, 100)) ;

     $formatted .= sprintf( "</pre>" );


    return $formatted ;

}