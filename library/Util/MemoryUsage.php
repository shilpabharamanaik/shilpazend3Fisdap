<?php
class Util_MemoryUsage
{
    public static function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024, ($i=floor(log($size, 1024)))), 2).' '.$unit[$i];
    }

    public static function getMemoryUsage()
    {
        return self::convert(memory_get_usage(true));
    }

    public static function logMemoryUsage($msg = "")
    {
        \Zend_Registry::get('logger')->debug(self::getMemoryUsage() . ": " . $msg);
    }
}
