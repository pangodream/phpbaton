<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 10:23
 */

namespace Baton;

use Baton\Request;
use Baton\Response;
use Dotenv\Dotenv;
use PhpSimpcli\CliParser;

class Master
{
    public function __construct(){
        $this->loadConfiguration();
        $this->evalInvokingOptions();
    }

    /**
     * Checks which option is specified when invoking from the command line
     */
    private function evalInvokingOptions(){
        $co = new CliParser();
        if($co->get('put')->found){
            if($co->get('put')->type == 'missing'){
                die("Error: you must specify a port to set the tasker\n");
            }
            $this->createTasker($co->get('put')->value);
        }
        if($co->get('ping')->found){
            if($co->get('ping')->type == 'missing'){
                die("Error: you must specify a port to ping to\n");
            }
            $this->ping($co->get('ping')->value);
        }
        if($co->get('kill')->found){
            if($co->get('kill')->type == 'missing'){
                die("Error: you must specify a port to kill tasker\n");
            }
            $this->kill($co->get('kill')->value);
        }
        if($co->get('poolStatus')->found){
            $this->poolStatus();
        }
        if($co->get('createAll')->found){
            $this->createAll();
        }
        if($co->get('killAll')->found){
            $this->killAll();
        }
        if($co->get('test')->found){
            $this->test($co->get('test')->value);
        }

    }
    private function test($mod){
        if($mod=='1') {
            $this->kill(6666);
            $this->createTasker(6666);
        }

        //$response = $this->request(6666, "NetTool", "getIP", array("domain"=>"www.pangodream.com"));
        //var_dump($response);
        //usleep(200000);
        $response = $this->request(6666, "Tasker", "sendResponse");
        var_dump($response);
    }

    /**
     * Shows the status of each of the taskers in the pool
     */
    private function poolStatus(){
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            echo "Tasker ".$port.": ";
            if($this->ping($port)){
                echo "Up\n";
            }else{
                echo "Down\n";
            }
        }
    }

    /**
     * Creates all possible taskers in the pool
     */
    private function createAll(){
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            echo "Tasker ".$port.": ";
            if($this->ping($port)){
                echo "Up\n";
            }else{
                echo "Down\n";
                $this->createTasker($port);
            }
        }
    }

    /**
     * Kills all the taskers in the pool
     */
    private function killAll(){
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            echo "Tasker ".$port.": ";
            if($this->ping($port)){
                echo "Up\n";
                $this->kill($port);
            }else{
                echo "Down\n";
            }
        }
    }

    /**
     * Pings the tasker  listening on specified port
     * @param $port
     * @param bool $quiet
     * @return bool
     */
    private function ping($port, $quiet = true){
        $ret = false;
        $response = $this->request($port,'Tasker','sendResponse');
        if($response !== false){
            if(!$quiet) echo "Ping succeed.\n";
            $ret = true;
        }else{
            if(!$quiet) echo "Ping failed\n";
        }
        return $ret;
    }

    /**
     * Kills the tasker listening on specified port
     * @param $port
     * @return bool
     */
    private function kill($port){
        $ret = false;
        if($this->ping($port)) {
            $response = $this->request($port, 'Tasker', 'suicide');
            if(!$this->ping($port)) {
                echo "Killing succeed.\n";
                $ret = true;
            } else {
                echo "Killing failed\n";
            }
        }else{
            echo "Tasker is not alive\n";
        }
        return $ret;
    }

    /**
     * Performs a request to the specified tasker
     * @param $class           Class to be loaded within the tasker
     * @param $method          Method to be invoked within the tasker
     * @param array $params    Parameters to be passed to the method
     * @return \Baton\Response
     */
    private function request($port, $class, $method, $params=array()){
        $response = false;
        $client = @stream_socket_client("tcp://127.0.0.1:".$port, $errno, $errstr);
        stream_set_timeout($client, 0, $_ENV['WAIT_FOR_RESPONSE']);
        if(!$client){
            //echo $errno." ".$errstr."\n";
        }else{
            $rq = new Request();
            $rq->setClass($class)->setMethod($method)->setParams($params);
            $sr = serialize($rq);
            fwrite($client, $sr);
            $serResponse = fread($client, 2048);
            $response = unserialize($serResponse);
        }
        return $response;
    }

    /**
     * Creates a tasker to listen in the specified port
     * @param $port
     * @return bool
     */
    public function createTasker($port){
        $ret = false;
        if($this->ping($port)){
            echo "Error: Tasker is already created.\n";
        }else{
            $phpExec = $_ENV['PHP_EXEC'];
            $cmd = $phpExec." ".realpath(__DIR__."/../")."/tasker.php -port ".$port;
            if (substr(php_uname(), 0, 7) == "Windows"){
                pclose(popen("start /B ". $cmd, "r"));
            }
            else {
                exec($cmd . " > /dev/null &");
            }
            $microSecs = (int) $_ENV['WAIT_FOR_NEW'];
            usleep($microSecs);
            if($this->ping($port)){
                echo "Tasker succesfully created.\n";
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * Loads the configuration specified in .env file
     */
    private function loadConfiguration(){
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
}