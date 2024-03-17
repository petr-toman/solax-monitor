<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__) . '/poll-curl.php';

$SlxDataSet = convertSolax();

//----
$solax_json = json_encode( $SlxDataSet,  JSON_PRETTY_PRINT );
echo ($solax_json );

//uložíme to do redisu, ať se ostatní mohou koukat bez nutnosti to znovu tvořit.
$redis = new Redis();
$redis->connect('cache-redis', 6379);
$redis->set( "SOLAX", $solax_json);
$redis->expire('SOLAX', 5);
exit(  );

//--------------//

