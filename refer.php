<?php 
$link = $_GET['link'];

$log_file = 'hits.txt';

$logs = json_decode(file_get_contents($log_file), true);

$hit_count = (isset($logs[$link])?$logs[$link]:0);

$logs[$link] = $hit_count + 1;

arsort($logs);

file_put_contents($log_file, json_encode($logs));

header('location:'.$link);