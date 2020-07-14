<?php

declare(strict_types=1);

spl_autoload_register('MyAutoloader::ClassLoader');

class MyAutoloader
{
    public static function ClassLoader($className)
    {
        while (strpos($className, '\\')) {
            $className = strstr($className, '\\');
            $className = substr($className, 1);
        }

        $path = __DIR__.'\\';
        $file = $path.$className.'.php';

        if (file_exists($file)) {
            include $file;
        }
    }
}
