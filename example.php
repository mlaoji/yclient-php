<?php
require dirname(__FILE__).'/YClient.php';
$c = YClient::getInstance("127.0.0.1:9002", "username","password");
//$c->setLogFile("logs/access.log");
try {
    $s = $c->request("passport/getUserInfo", array("uid"=>"1"));
    var_dump($s);
} catch(YClientException $e) {
    echo "error: [". $e->getCode() . "] ". $e->getMessage();
}

