<?php
/**
 * Created by Pangodream.
 * Date: 03/11/2018
 * Time: 10:13
 */
require_once __DIR__.'/../vendor/autoload.php';

use Baton\Master;
use PhpSimpcli\CliParser;
use Dotenv\Dotenv;
use Baton\Error;

$error = new Error();
set_error_handler(array($error, 'handler'),E_ALL|E_STRICT|E_WARNING);

$master = new Master();
