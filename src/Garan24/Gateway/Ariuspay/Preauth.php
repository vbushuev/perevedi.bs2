<?php
namespace Garan24\Gateway\Ariuspay;
use \Garan24\Gateway\BaseConnector as BaseConnector;
use \Garan24\Gateway\Ariuspay\PreauthRequest as Request;
use \Garan24\Gateway\Ariuspay\Exception  as Garan24GatewayAruispayException;
class Preauth extends BaseConnector{
    public function __construct($data=[]){
        $this->_request = new Request($data);
    }
};
?>
