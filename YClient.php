<?php
require dirname(__FILE__).'/vendor/autoload.php';

include_once dirname(__FILE__).'/lib/Ygoservice/YClient.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Reply.php';
include_once dirname(__FILE__).'/lib/Ygoservice/Request.php';
include_once dirname(__FILE__).'/lib/GPBMetadata/Service.php';

Class YClient
{
    private $host;
    const ERROR_INVALID_RES = "response empty!";
    
    public function __construct($host) {/*{{{*/
        $this->host = $host;
    }/*}}}*/

    public static function getInstance($host) {/*{{{*/
        return new self($host);
    }/*}}}*/

    public function request($method, $params = array()) {/*{{{*/
        $client = new Ygoservice\YGOClient($this->host, [
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
                ]);

        $request = new Ygoservice\Request();
        $request->setMethod($method);
        $request->setParams($params);

        list($reply, $status) = $client->Call($request)->wait();
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
}


Class YClientException extends RuntimeException
{
    public function __construct($msg, $code = "-1") {
        parent::__construct($msg, $code);
    }
}
