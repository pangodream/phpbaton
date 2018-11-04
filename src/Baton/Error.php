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
        if (!in_array($errno, array(2))){
            $str = date('Y-m-d H:i:s')."\n";
            $str .= "Error:\n  $errno\n  $errstr\n  $errfile\n  $errline\n\n";
            file_put_contents("error_log.txt", $str, FILE_APPEND);
        }
    }
}