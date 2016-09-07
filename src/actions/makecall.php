<?php
require realpath(dirname(__FILE__).'/../vendor/autoload.php');

use PAMI\Client\Impl\ClientImpl;
use PAMI\Listener\IEventListener;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Action\ListCommandsAction;
use PAMI\Message\Action\ListCategoriesAction;;
use PAMI\Message\Action\OriginateAction;
use \classes\Config;
use \classes\Log;

class A implements IEventListener
{
    public function handle(EventMessage $event)
    {
        var_dump($event);
    }
}

if($_GET['secret'] != Config::get('Vtiger.VtigerSecretKey')){
	Log::Critical("makecall","wrong vtiger key");
    require "../index.php";
    die();
}

if ( ! empty( $_GET['to']  ) )
try {
    $options = Config::get('asterisk');
    $a = new ClientImpl($options);
    $a->registerEventListener(new A());
	$a->open();

    $originateMsg = new OriginateAction("SIP/".$_GET['from']);
    $originateMsg->setContext($_GET['context']);
    $originateMsg->setPriority('1');
    $originateMsg->setExtension($_GET['to']);
    $originateMsg->setCallerID($_GET['to']);
    $originateMsg->setAsync(Config::get('OutgoingCalls.Async'));

    $a->send($originateMsg);
    $a->close();
} catch (Exception $e) {
	// Log::Critical("test",print_r($e,true),__FILE__,__LINE__);
	echo( print_r($e,true));
}


//TODO: implement outgoing call
//params: from = extension, to = phone number, context = outbound context (outcoming-sip)