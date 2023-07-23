<?php 
$link = $_GET['link'];

$log_file = 'hits.log';
$logs = json_decode(file_get_contents($log_file), true);

$hit_count = $logs[$link];

$logs[$link] = $hit_count + 1;

file_put_contents($log_file, json_encode($logs));

header('location:'.$link);