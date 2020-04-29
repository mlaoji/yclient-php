<?php
namespace Ygoservice;

class YGOClient extends \Grpc\BaseStub{
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    public function Call(\Ygoservice\Request $argument,$metadata=[],$options=[]){
        //ygoservice.YGOService/Call 和 proto 文件定义保持一致
        return $this->_simpleRequest('ygoservice.YGOService/Call',
            $argument,
            ['\Ygoservice\Reply', 'decode'],
            $metadata, $options);
    }
}
