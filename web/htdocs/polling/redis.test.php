<?php
$redis = new Redis();
$redis->connect('cache-redis', 6379);

echo "Connection to server successfully <br>";
echo "Server is running: " . $redis->ping() . "<br>";

echo $redis->get("TOMAN");




?>