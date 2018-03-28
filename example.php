<?php
require dirname(__FILE__).'/YClient.php';
$c = YClient::getInstance("passport.inner.chaoqun.mobi:9202", "site","3sdF9k4HShLh");
//$c->setLogFile("logs/access.log");
try {
    $s = $c->request("passport/getUserInfo", array("uid"=> 2));
    var_dump($s);
} catch(YClientException $e) {
    echo "error: [". $e->getCode() . "] ". $e->getMessage();
}

