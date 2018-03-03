<?php
require dirname(__FILE__).'/YClient.php';
$c = YClient::getInstance("172.16.1.170:6001", "account","3sdF9k4HShLh");
try {
    $s = $c->request("counter/setCounter", array("name"=>"following", "rid"=> 14, "num"=>2));
    var_dump($s);
} catch(YClientException $e) {
    echo "error: [". $e->getCode() . "] ". $e->getMessage();
}

