<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
header('Content-Type: application/json; charset=utf-8');

// this is called repeatedly by ../scripts/start-pollin.sh
// to provide regular data retrieval


printf( "---Service Polling -----" );

//data retrieval function call here//

//data put to redis cache here //

//data put to database here //


printf( "- Up And Running-----------\n" );

exit( );