<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 10:13
 */
require_once __DIR__.'/../vendor/autoload.php';

use Baton\Master;
use PhpSimpcli\CliParser;
use Baton\Common;

Common::getReady();

$wrapper = new Wrapper();
$co = new CliParser();
if($co->get('put')->found){
    if($co->get('put')->type == 'missing'){
        echo "Error: you must specify a port to set the tasker\n";
        die();
    }
    $wrapper->createTasker($co->get('put')->value);
}
if($co->get('ping')->found){
    if($co->get('ping')->type == 'missing'){
        echo "Error: you must specify a port to ping to\n";
        die();
    }
    $wrapper->ping($co->get('ping')->value);
}
if($co->get('kill')->found){
    if($co->get('kill')->type == 'missing'){
        echo "Error: you must specify a port to kill tasker\n";
        die();
    }
    $wrapper->kill($co->get('kill')->value);
}
if($co->get('poolStatus')->found){
    $wrapper->poolStatus();
}
if($co->get('createAll')->found){
    $wrapper->createAll();
}
if($co->get('killAll')->found){
    $wrapper->killAll();
}

/*
 * Wrapper class
 */
class Wrapper{
    private $master = null;
    public function __construct()
    {
        $this->master = new Master();
    }

    function createTasker($port)
    {
        $res = $this->master->createTasker($port);
    }

    function ping($port)
    {
        $res = $this->master->ping($port);
        if ($res) {
            $this->echoLine("Ping suceed");
        } else {
            $this->echoLine("Ping failed");
        }
    }

    function kill($port)
    {
        if ($this->master->ping($port)) {
            $res = $this->master->kill($port);
            if ($res) {
                $this->echoLine("Killing suceed");
            } else {
                $this->echoLine("Killing failed");
            }
        } else {
            $this->echoLine("Tasker is not alive");
        }
    }

    function poolStatus()
    {
        $res = $this->master->poolStatus();
        foreach ($res as $tasker) {
            $this->echoLine("Tasker (" . $tasker['port'] . ") is " . $tasker['status']);
        }
    }

    function createAll()
    {
        $this->echoLine("Creating all taskers...");
        $res = $this->master->createAll();
        foreach ($res as $tasker) {
            $this->echoLine("Tasker (" . $tasker['port'] . ") is " . $tasker['status']);
        }
    }

    function killAll()
    {
        $this->echoLine("Killing all taskers...");
        $res = $this->master->killAll();
        foreach ($res as $tasker) {
            $this->echoLine("Tasker (" . $tasker['port'] . ") is " . $tasker['status']);
        }
    }

    /**
     * Echoes a text line and a break line
     * @param $line
     */
    function echoLine($line){
        echo $line."\n";
    }
}
