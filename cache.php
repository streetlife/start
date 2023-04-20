<?php 
ob_start();
require 'links.php';
file_put_contents('index.html', ob_get_contents());
ob_end_flush();
?>