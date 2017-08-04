<?php
namespace Garan24\Gateway\Ariuspay;
use \Garan24\Gateway\BaseConnector as BaseConnector;
use \Garan24\Gateway\Ariuspay\Request as AriuspayRequest;
use \Garan24\Gateway\Aruispay\Exception  as Garan24GatewayAruispayException;
class Connector extends BaseConnector{
    public function __construct($req=false){
        if($req!==false) $this->setRequest($req);
    }
    public function setRequest($req){
        if($req instanceof AriuspayRequest) $this->_request = $req;
        else throw new Garan24GatewayAruispayException("Object ".preg_replace("/\\\/",".",get_class($req))." is not instance of Ariuspay Request object.",500);
    }
}
?>
