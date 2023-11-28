<?php

namespace Aatis\Core\Service;

class ViewLoader
{
    public static function load($pathToFile)
    {
        $view = dirname(__DIR__).'/views/'.$pathToFile.'.php';

        if (file_exists($view)) {
            require_once $view;
        } else {
            header('HTTP/1.0 404 Not Found');
            require_once dirname(__DIR__).'/views/errors/404.php';
        }
    }
}
