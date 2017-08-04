<?php
namespace Garan24\Gateway\Finkarta;
use \Garan24\Garan24 as Garan24;
use \Garan24\Gateway\Finkarta\Exception as Garan24GatewayException;
use \Garan24\Gateway\Finkarta\Request as FinkartaRequest;
use \Garan24\Gateway\Finkarta\Response as FinkartaResponse;
class Connector implements \Garan24\Interfaces\IConnector{
    public function call($request){

        $response = $this->query($request->getUrl(),["file"=>'@/'.$request->getRequestFile()]);
        return $response;
    }
    public function query($url,$data = null){
        $fp=fopen('../curl-'.date("Y-m-d").'.log', 'a+');
        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_VERBOSE => 1,
            CURLOPT_STDERR => $fp,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ];
        $curl = curl_init($url);
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        Garan24::debug("RAW RESPONSE:[{$response}]");
        return $response;
    }
};
?>
