<?php
class CachingClass
{
    var $cache_time  =   null;
    var $file_name   =   null;
    function __construct( $file_name, $cache_time = 0 )
    {
         $this->cache_time =  ( $cache_time > 0 ) ? $cache_time : (300 * 60);
         $this->file_name  =  $file_name;
    }
    function startBuffering( )
    {
        ob_start();
    }
    function stopBuffering( )
    {
        $fp     =   fopen($this->file_name, 'w'); 
        fwrite($fp, ob_get_contents()); 
        fclose($fp);
        ob_end_flush();
    }
    function check()
    { 
        return(file_exists($this->file_name) && (time() - $this->cache_time < filemtime($this->file_name)));
    }
}
?>