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
