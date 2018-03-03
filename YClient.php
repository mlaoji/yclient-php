<?php
require dirname(__FILE__).'/vendor/autoload.php';

include_once dirname(__FILE__).'/lib/Ygoservice/YClient.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Reply.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Request.php';
include_once dirname(__FILE__).'/lib/GPBMetadata/Service.php';

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

    public function request($method, $params = array()) {/*{{{*/
        $params["appid"]  = $this->appid;
        $params["secret"] = $this->secret;
        $params["guid"] = $this->_getGuid();

        $request = new Ygoservice\Request();
        $request->setMethod($method);
        $request->setParams($params);

        list($reply, $status) = $this->client->Call($request)->wait();
        if("NULL" == (gettype($reply))) {
            throw new YClientException(self::ERROR_INVALID_RQT);
        }

        return $this->_parseResponse($reply->getResponse());
    }/*}}}*/

    public function _parseResponse($response) {/*{{{*/
        $res = json_decode($response, true);

        if(!isset($res["code"])) {
            throw new YClientException(self::ERROR_INVALID_RES);
        }

        if($res["code"] > 0) {
            throw new YClientException($res["msg"], $res["code"]);
        }

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
