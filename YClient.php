<?php
require dirname(__FILE__).'/vendor/autoload.php';

include_once dirname(__FILE__).'/lib/Ygoservice/YClient.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Reply.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Request.php';
include_once dirname(__FILE__).'/lib/GPBMetadata/Service.php';
include_once dirname(__FILE__).'/lib/YLogger.php';

!isset($GLOBALS['YC_LOG']) && $GLOBALS['YC_LOG'] = array(
    'level'    => YLogger::LOG_LEVEL_WARN, //日志级别为警告级别，同时业务日志（调试级别）将关闭
    'split'    => YLogger::LOG_SPLIT_DAY,
    'logfile' => dirname(__FILE__) . '/logs/yclient.log',
);

Class YClient
{
    private $host;
    private $appid;
    private $secret;
    private $client;

    const ERROR_INVALID_RES = "response empty!";
    const ERROR_INVALID_RQT = "request error!";
    
    public function __construct($host, $appid = null, $secret = null) {/*{{{*/
        $this->host   = $host;
        $this->appid  = $appid;
        $this->secret = $secret;
        
        $this->client = new Ygoservice\YGOClient($this->host, [
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
                ]);
    }/*}}}*/

    public static function getInstance($host, $appid = null, $secret = null) {/*{{{*/
        return new self($host, $appid, $secret);
    }/*}}}*/

    public static function setLogFile($logfile) {/*{{{*/
        $GLOBALS["YC_LOG"]["logfile"] = $logfile;
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

        list($reply, $status) = $this->client->Call($request)->wait();
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
