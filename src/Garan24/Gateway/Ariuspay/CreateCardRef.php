<?php
namespace Garan24\Gateway\Ariuspay;
use \Garan24\Gateway\BaseConnector as BaseConnector;
use \Garan24\Gateway\Ariuspay\CreateCardRefRequest as CreateCardRefRequest;
//use \Garan24\Gateway\Ariuspay\GetCardRefResponse as Response;
use \Garan24\Gateway\Aruispay\Exception  as Garan24GatewayAruispayException;
class CreateCardRef extends BaseConnector{
    public function __construct($data=[]){
        $this->_request = new CreateCardRefRequest($data);
        //$this->_operation = "get-card-info";
    }
}
?>
