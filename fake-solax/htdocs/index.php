<?php
header("Content-type:application/json");
$file = file_get_contents('./index.json', true);
echo $file ;