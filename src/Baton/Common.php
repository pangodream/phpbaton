<?php
/**
 * Created by Pangodream.
 * User: Development
 * Date: 05/11/2018
 * Time: 12:53
 */

namespace Baton;
use Dotenv\Dotenv;

class Common
{
    public static function getReady(){
        self::loadConfiguration();
        self::setErrorHandler();
    }
    /**
     * Loads the configuration specified in .env file
     */
    public static function loadConfiguration(){
        //Check if configuration file exists
        if(!file_exists(__DIR__.'/../../.env')){
            echo "Configuration file .env has not been found.\n";
            echo "You should place .env file (or edit and rename .env.example) in the folder\n";
            echo realpath(__DIR__.'/../../')."\n";
            die();
        }else{
            $dotenv = new Dotenv(realpath(__DIR__.'/../../'), '.env');
            $dotenv->load();
        }
    }
    public static function setErrorHandler(){
        set_error_handler(array('Baton\Common', 'errorHandler'),E_ALL|E_STRICT|E_WARNING);
    }
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if(isset($GLOBALS['MOD_NAME'])) {
            $logFile = 'tmp/' . $GLOBALS['MOD_NAME'] . '_err.log.txt';
        }else{
            $logFile = 'tmp/' . 'gen' . '_err.log.txt';
        }
        //During development stage we want errors to be dumped to a log file
        //We ignore warnings (level = 2) as there are many of them because of sockets stream library
        if (!in_array($errno, array(2))){
            $str = date('Y-m-d H:i:s')."\n";
            $str .= "Error:\n  $errno\n  $errstr\n  $errfile Line: $errline\n\n";
            file_put_contents($logFile, $str, FILE_APPEND);
        }
    }
}