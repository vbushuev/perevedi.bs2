<?php
namespace Garan24\Gateway\Ariuspay;
use \Garan24\Gateway\BaseConnector as BaseConnector;
use \Garan24\Gateway\Ariuspay\GetCardRefRequest as Request;
//use \Garan24\Gateway\Ariuspay\GetCardRefResponse as Response;
use \Garan24\Gateway\Aruispay\Exception  as Garan24GatewayAruispayException;
class GetCardRef extends BaseConnector{
    public function __construct($data=[]){
        $this->_request = new Request($data);
        //$this->_operation = "get-card-info";
    }
}
?>
