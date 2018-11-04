<?php
/**
 * Created by Pangodream.
 * Date: 04/11/2018
 * Time: 11:31
 */

namespace Baton;
use Baton\Response;

class External
{
    public static function reply($data=array(), $code=0, $description = "OK"){
        $response = new \Baton\Response();
        return $response->init()
                        ->setResultCode($code)
                        ->setResultDescription($description)
                        ->setResultData($data);
    }
}