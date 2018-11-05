<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 10:12
 */

namespace Baton;


use PhpSimpcli\CliParser;
use Baton\Common;

class Tasker
{
    private $sock = null;
    private $conn = null;
    private $taskerPort = null;
    private $doListen = true;
    private $response = null;
    private $startTime = null;
    private $endTime = null;
    private $cnf = null;

    public function __construct(){
        $GLOBALS['MOD_NAME'] = 'tasker';
        Common::getReady();
        $this->response = new Response();
        $this->response->init();
        $co = new CliParser();
        if($co->get('port')->found){
            $port = $co->get('port')->value;
            $this->listenOn($port);
        }else{
            echo "You must specify a listening port with -port option\n";
            exit(1);
        }
    }
    private function listenOn($port){
        //try to open a free listening port
        $this->sock = stream_socket_server("tcp://0.0.0.0:".$port, $errno, $errstr);
        if (!$this->sock) {
            echo "$errstr ($errno)<br />\n";
            exit(1);
        } else {
            $this->taskerPort = $port;
            echo "Listening on $this->taskerPort\n";
            while ($this->doListen){
                while ($this->conn = @stream_socket_accept($this->sock, -1)) {
                    $this->read();
                    @fclose($this->conn);
                }
            }
            exit(0);
        }
    }

    private function read(){
        //read incoming request
        $serRequest = fread($this->conn, 2048);
        /*
         * @var Request $request
         */
        $request = unserialize($serRequest);
        $class = get_class($request);
        if($class == "Baton\Request"){
            if($request->getClass() !== 'Tasker' || $request->getMethod() !== 'sendResponse'){
                $this->response = new Response();
                $this->response->init()->setResultCode(0)->setResultDescription("Request accepted");
                $this->sendResponse();
                @fclose($this->conn);
                $this->doAction($request);
            }else{
                $this->sendResponse();
                @fclose($this->conn);
            }
        }else{
            $this->response->init()->setResultCode(99)->setResultDescription("Unknown request type");
            $this->sendResponse();
            @fclose($this->conn);
        }
    }
    private function sendResponse(){
        $serResponse = serialize($this->response);
        fwrite($this->conn, $serResponse);
    }
    /**
     * @param Request $request
     */
    private function doAction($request){
        $this->response = new Response();
        $class = $request->getClass();
        $method = $request->getMethod();
        if($class != 'Tasker') {
            if (file_exists(__DIR__ . '/../external/' . $class . '.php')) {
                require_once __DIR__ . '/../external/' . $class . '.php';
            }
        }
        if($class == 'Tasker'){
            $class = $this;
        }
        if(is_callable(array($class, $method))){
            $this->invokeMethod($class, $method, $request->getParams());
        }else{
            $this->response->
                   init()->
                   setResultCode(10)->
                   setResultDescription("Unable to invoke class $class / method $method");
        }
    }
    private function invokeMethod($className, $methodName, $parameters){
        $p = array();
        $r = new \ReflectionMethod($className, $methodName);
        $declaredParams = $r->getParameters();
        foreach ($declaredParams as $dParam) {
            $name = $dParam->getName();
            $optional =  $dParam->isOptional();
            if($optional){
                $default =  $dParam->getDefaultValue();
            }else{
                $default = null;
            }
            $value = null;
            if(isset($parameters[$name])){
                $value = $parameters[$name];
            }else{
                $value = $default;
            }
            $p[$name] = $value;
        }
        $this->startTime = round(microtime(true)*1000);
        //echo "Invoking $className $methodName\n";
        /*
         * @var Baton\Response $response
         */
        $response = call_user_func_array(array($className, $methodName), $p);
        if($response !== null) {
            $this->endTime = round(microtime(true) * 1000);
            $response->setExecutionTime($this->endTime - $this->startTime);
        }else{
            $response = new Response();
            $response->init()->setResultCode(98)->setResultDescription("Error invoking $methodName");
            //echo "Error invoking $className $methodName\n";
        }
        $this->response = $response;
    }
    private function suicide(){
        die();
    }
    private function testSleep($seconds){
        $response = new Response();
        sleep($seconds);
        $response->init()->setResultCode(0)->setResultDescription("Have slept for ".$seconds." seconds");
        return $response;
    }
}