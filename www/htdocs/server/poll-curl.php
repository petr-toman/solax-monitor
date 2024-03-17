<?php

function pollSolaxData()
{
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
    logdata("data-solax", $solaxResult);
    return json_decode($solaxResult);
}

function pollAZrouterData()
{
    // Volání API AZ Router pomocí cURL

    $AZRouterPowerUrl = getenv("AZRouterPowerUrl");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $AZRouterPowerUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $AZresult = curl_exec($ch);
    curl_close($ch);
    logdata("data-AZrouter", $AZresult);
    return  json_decode($AZresult);
}

function logdata($fp, $data)
{

    // Uložení data do souboru v log adresáři, podle úrovně:

    $Debuglevel = getenv("Debuglevel");
    $fname = dirname(__DIR__, 1) . "/../logs/$fp";

    if ($Debuglevel == 1) {
        echo  $data;
    } elseif ($Debuglevel == 2) {
        file_put_contents("$fname.json",  $data);
    } elseif ($Debuglevel == 3) {
        file_put_contents("$fname.json", PHP_EOL . $data, FILE_APPEND);
    } elseif ($Debuglevel == 4) {
        $dt = date("Y-m-d THis");
        file_put_contents("$fname-$dt.json",  $data,);
    }
}

function unsignedToSigned($val)
{
    // Funkce pro převod unsigned na signed
    return ($val > 32767) ? $val - 65536 : $val;
}


function convertSolax()
{

    $solaxData = pollSolaxData();
    $AZRData   = pollAZrouterData();

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



    $SlxDataSet = json_decode(file_get_contents("format.json", true));
    $SlxDataSet->CurrentTimeStamp = date("Y-m-d H:i:s T");

    $SlxDataSet->EnvConstants->SolaxUrl =  getenv('SolaxUrl');
    //$SlxDataSet->EnvConstants->SolaxRegNr =  getenv('SolaxRegNr');
    $SlxDataSet->EnvConstants->SolaxRegNr =  $solaxData->sn;
    $SlxDataSet->EnvConstants->SolaxString1Peak =  getenv('SolaxString1Peak');
    $SlxDataSet->EnvConstants->SolaxString2Peak =  getenv('SolaxString2Peak');
    $SlxDataSet->EnvConstants->SolaxTotalPeak = getenv('SolaxString1Peak') + getenv('SolaxString2Peak');
    //$SlxDataSet->EnvConstants->SolaxInverterMaxPower =  getenv('SolaxMaxPower');
    $SlxDataSet->EnvConstants->SolaxInverterMaxPower =  $solaxData->Information[0]*1000;
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
    $SlxDataSet->Battery->batteryPower->value =  unsignedToSigned($solaxData->Data[41]);
    $SlxDataSet->Battery->totalChargedIn->value =  $solaxData->Data[79] / 10;
    $SlxDataSet->Battery->totalChargedOut->value =  $solaxData->Data[78] / 10;


    //----
    $SlxDataSet->inverter->inverterMode->value   = $solaxData->Data[19] . ' - ' . $inverterModeMap[$solaxData->Data[19]] ;
    $SlxDataSet->inverter->inverterTemp->value   = $solaxData->Data[54];
    $SlxDataSet->inverter->inverterPower->value  =  unsignedToSigned($solaxData->Data[9]);
    $SlxDataSet->inverter->powerL1->value  =  $solaxData->Data[6];
    $SlxDataSet->inverter->powerL2->value  =  $solaxData->Data[7];
    $SlxDataSet->inverter->powerL3->value  =  $solaxData->Data[8];
    $SlxDataSet->inverter->totalProductionInclBatt->value  =  $solaxData->Data[70] / 10;

    //----
    $SlxDataSet->Grid->feedInPower->value  =  unsignedToSigned($solaxData->Data[34]);
    $SlxDataSet->Grid->AZRPowerL1->value  = $AZRData->input->power[0]->value;
    $SlxDataSet->Grid->AZRPowerL2->value  = $AZRData->input->power[1]->value;
    $SlxDataSet->Grid->AZRPowerL3->value  = $AZRData->input->power[2]->value;
    $SlxDataSet->Grid->AZRPowerTotal->value  = $AZRData->input->power[0]->value + $AZRData->input->power[1]->value +  $AZRData->input->power[2]->value;
    $SlxDataSet->Grid->totalGridIn->value   =  ($solaxData->Data[93] * 65536 + $solaxData->Data[92]) / 100;
    $SlxDataSet->Grid->totalGridOut->value   =  ($solaxData->Data[91] * 65536 + $solaxData->Data[90]) / 100;
    //----

    $SlxDataSet->Home->loadHome->value   = unsignedToSigned($solaxData->Data[47]);
    $SlxDataSet->Home->loadHomeL1->value   = $SlxDataSet->inverter->powerL1->value - $SlxDataSet->Grid->AZRPowerL1->value;
    $SlxDataSet->Home->loadHomeL2->value   = $SlxDataSet->inverter->powerL2->value - $SlxDataSet->Grid->AZRPowerL2->value;
    $SlxDataSet->Home->loadHomeL3->value   = $SlxDataSet->inverter->powerL3->value - $SlxDataSet->Grid->AZRPowerL3->value;
    $SlxDataSet->Home->totalConsumption->value   = $SlxDataSet->Grid->totalGridIn->value + $SlxDataSet->inverter->totalProductionInclBatt->value - $SlxDataSet->Grid->totalGridOut->value;
    if ($SlxDataSet->Home->totalConsumption->value == 0) {
        $SlxDataSet->Home->selfSufficiencyRate->value   = 0;
    } else {
        $SlxDataSet->Home->selfSufficiencyRate->value  = ($SlxDataSet->inverter->totalProductionInclBatt->value - $SlxDataSet->Grid->totalGridOut->value) * 100 / $SlxDataSet->Home->totalConsumption->value;
    }

    //exp
    $SlxDataSet->EXP->expl1 = $SlxDataSet->Grid->AZRPowerL1->value;
    $SlxDataSet->EXP->expl2 = $SlxDataSet->Grid->AZRPowerL2->value;
    $SlxDataSet->EXP->expl3 = $SlxDataSet->Grid->AZRPowerL3->value;
    $SlxDataSet->EXP->expl123 = $SlxDataSet->EXP->expl1  + $SlxDataSet->EXP->expl2 + $SlxDataSet->EXP->expl3;

    $SlxDataSet->EXP->exp34 =  unsignedToSigned($solaxData->Data[34]);
    $SlxDataSet->EXP->exp35 =  unsignedToSigned($solaxData->Data[35]);

    $SlxDataSet->EXP->exp47_loadhome =  unsignedToSigned($solaxData->Data[47]);
    $SlxDataSet->EXP->exp48 =  unsignedToSigned($solaxData->Data[48]);
    $SlxDataSet->EXP->exp49 =  unsignedToSigned($solaxData->Data[49]);
    $SlxDataSet->EXP->exp50 =  unsignedToSigned($solaxData->Data[50]);
    $SlxDataSet->EXP->exp51 =  unsignedToSigned($solaxData->Data[51]);
    $SlxDataSet->EXP->exp52 =  unsignedToSigned($solaxData->Data[52]);
    $SlxDataSet->EXP->exp68 =  unsignedToSigned($solaxData->Data[68]);
    $SlxDataSet->EXP->exp68 =  unsignedToSigned($solaxData->Data[68]);
    $SlxDataSet->EXP->exp86 =  unsignedToSigned($solaxData->Data[86]);
    $SlxDataSet->EXP->exp88 =  unsignedToSigned($solaxData->Data[88]);

    logdata("data-converted", json_encode( $SlxDataSet )  );

    return $SlxDataSet;
}
