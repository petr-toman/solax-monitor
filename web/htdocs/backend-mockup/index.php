<?php
header("Content-type:application/json");


$counter = file_get_contents("./lastcall" , true);

if( isset( $counter ) ) {
    $counter  += 1;
 } else {
    $counter = 1;
 }
if ( $counter  > 3 ){
    $counter = 1;
}

file_put_contents("./lastcall" , $counter );
$filename = "data0".$counter.".json";

$file = file_get_contents( $filename , true);
echo $file ;