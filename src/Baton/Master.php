<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 10:23
 */

namespace Baton;

use Baton\Request;
use Baton\Response;
use PhpSimpcli\CliParser;
use Baton\Common;

class Master
{
    private $cnf = null;
    public function __construct(){
        $GLOBALS['MOD_NAME'] = 'master';
        Common::getReady();
    }
    /**
     * Shows the status of each of the taskers in the pool
     */
    public function poolStatus(){
        $pool = array();
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            if($this->ping($port)){
                $status = 'Up';
            }else{
                $status = 'Down';
            }
            $pool[] = array('port' => $port, 'status' => $status);
        }
        return $pool;
    }

    /**
     * Creates all possible taskers in the pool
     */
    public function createAll(){
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            if(!$this->ping($port)){
                $this->createTasker($port);
            }
        }
        return $this->poolStatus();
    }

    /**
     * Kills all the taskers in the pool
     */
    public function killAll(){
        $start = $_ENV['POOL_START'];
        $size = $_ENV['POOL_SIZE'];
        for($i = 0; $i < $size; $i++){
            $port = $start + $i;
            if($this->ping($port)){
                $this->kill($port);
            }
        }
        return $this->poolStatus();
    }

    /**
     * Pings the tasker  listening on specified port
     * @param $port
     * @param bool $quiet
     * @return bool
     */
    public function ping($port){
        $ret = false;
        $response = $this->request($port,'Tasker','sendResponse');
        if($response !== false){
            $ret = true;
        }
        return $ret;
    }

    /**
     * Kills the tasker listening on specified port
     * @param $port
     * @return bool
     */
    public function kill($port){
        $ret = false;
        if($this->ping($port)) {
            $response = $this->request($port, 'Tasker', 'suicide');
            if(!$this->ping($port)) {
                $ret = true;
            }
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
}