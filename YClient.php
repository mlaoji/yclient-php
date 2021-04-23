<?php
require dirname(__FILE__).'/vendor/autoload.php';

include_once dirname(__FILE__).'/config/YConfig.php';
include_once dirname(__FILE__).'/lib/Ygoservice/YClient.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Reply.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Request.php';
include_once dirname(__FILE__).'/lib/GPBMetadata/Service.php';
include_once dirname(__FILE__).'/lib/YLogger.php';

Class YClient
{
    private static $instance;
    private $host;
    private $appid;
    private $secret;
    private $client;

    const ERROR_INVALID_RES  = "response empty!";
    const ERROR_INVALID_RQT  = "request error!";
    const ERROR_INVALID_CONF = "config not exist!";
    
    public function __construct($project) {/*{{{*/
        if(!isset(YConfig::$app_conf[$project])) {
            throw new YClientException(self::ERROR_INVALID_CONF);
        }

        $this->host   = YConfig::$app_conf[$project]["host"];
        $this->appid  = YConfig::$app_conf[$project]["appid"];
        $this->secret = YConfig::$app_conf[$project]["secret"];
       
        $this->client = new Ygoservice\YGOClient($this->host, [
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
                ]);
    }/*}}}*/

    public static function getInstance($project) {/*{{{*/
        if (!isset(self::$instance[$project])) {
            self::$instance[$project]= new self($project);
        }

        return self::$instance[$project];
    }/*}}}*/

    public function request($method, $params = array()) {/*{{{*/
        foreach($params as $k => $v) {
            if(is_null($v)) {//null 值时, 可能会出现: PHP message: PHP Fatal error:  Given value cannot be converted to string 
                $params[$k] = "";
            }
        }

        $params["appid"]  = $this->appid;
        $params["secret"] = $this->secret;
        $params["guid"] = $this->_getGuid();

        $request = new Ygoservice\Request();
        $request->setMethod($method);
        $request->setParams($params);

        $params["method"] = $method;

        list($reply, $status) = $this->client->Call($request, ["appid" => [$this->appid], "secret" => [$this->secret], "guid" => [$params["guid"]]])->wait();
        if("NULL" == (gettype($reply))) {
            YLogger::warn("request error", 0, $params);
            throw new YClientException(self::ERROR_INVALID_RQT);
        }

        return $this->_parseResponse($params, $reply->getResponse());
    }/*}}}*/

    public function _parseResponse($params, $response) {/*{{{*/
        $res = json_decode($response, true);

        if(!isset($res["code"])) {
            YLogger::warn($response, 0, $params);
            throw new YClientException(self::ERROR_INVALID_RES);
        }

        if($res["code"] > 0) {
            YLogger::warn($response, $res["code"], $params);
            throw new YClientException($res["msg"], $res["code"]);
        }

        YLogger::access($response, $res["code"], $params);

        if(isset($res["data"])) {
            return $res["data"];
        }

        return null;
    }/*}}}*/

    public function _getGuid() {/*{{{*/
        return md5(microtime() . rand(1, 100000000));
    }/*}}}*/
}

Class YClientException extends RuntimeException
{
    public function __construct($msg, $code = "-1") {
        parent::__construct($msg, $code);
    }
}
