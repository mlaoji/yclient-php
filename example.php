<?php
require dirname(__FILE__).'/YClient.php';
$c = YClient::getInstance("172.16.1.170:6001");
try {
    $s = $c->request("counter/getCounter", array("app" => "account", "name"=>"like", "rid"=> 1, "num"=>2));
    var_dump($s);
} catch(YClientException $e) {
    echo "error: [". $e->getCode() . "] ". $e->getMessage();
}

