<?php
require dirname(__FILE__).'/YClient.php';
$c = YClient::getInstance("cmn_passport");
//$c->setLogFile("logs/access.log");
try {
    $s = $c->request("passport/getUsersInfo", array("name"=>"user","uid"=>1,"field"=>"a"));
    var_dump($s);
    exit;
    //$s = $c->request("counter/setCounter", array("name"=>"user","rid"=>1,"field"=>"a","num"=>1));
    $s = $c->request("counter/getCounter", array("name"=>"user","rid"=>1,"field"=>"a"));
    var_dump($s);
    $s = $c->request("counter/subCounter", array("name"=>"user","rid"=>1,"field"=>"a","num"=>1));
    var_dump($s);
    $s = $c->request("counter/getCounter", array("name"=>"user","rid"=>1,"field"=>"a"));
    var_dump($s);
} catch(YClientException $e) {
    echo "error: [". $e->getCode() . "] ". $e->getMessage();
}

