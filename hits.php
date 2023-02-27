<?php
$log_file = 'hits.txt';
$logs = json_decode(file_get_contents($log_file), true);

foreach ($logs as $key=>$log) {
    echo $key . ': ' . $log . '<br>';
}