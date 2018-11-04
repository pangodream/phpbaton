<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 12:44
 * This is just an example of a class to be invoked from a tasker
 */
use Baton\External;

class NetTool extends External
{
    public static function getIP($domain='www.pangodream.com'){
        //Get the IP of the specified domain
        $ip = gethostbyname($domain);
        //Lets waste some time to make things clearer
        sleep(5);
        //Invoke parent's method 'reply' with the results
        return self::reply(array('domain'=>$domain, 'IP'=>$ip));
    }
}