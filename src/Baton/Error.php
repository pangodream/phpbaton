<?php
/**
 * Created by Pangodream.
 * Date: 04/11/2018
 * Time: 9:59
 */

namespace Baton;


class Error
{
    public function handler($errno, $errstr, $errfile, $errline)
    {
        //During development stage we want errors to be dumped to a log file
        //We ignore warnings (level = 2) as there are many of them because of sockets stream library
        if (!in_array($errno, array(2))){
            $fileName = $_ENV['ERROR_LOG_PATH'].'/'.$_ENV['ERROR_LOG_FILE'];
            $str = date('Y-m-d H:i:s')."\n";
            $str .= "Error:\n  $errno\n  $errstr\n  $errfile Line: $errline\n\n";
            file_put_contents($fileName, $str, FILE_APPEND);
        }
    }
}