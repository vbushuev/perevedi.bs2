<?php
namespace Garan24\Gateway\Finkarta;
use \Garan24\Garan24 as Garan24;
use \Garan24\Gateway\Finkarta\Exception as Garan24GatewayException;
use \Garan24\Gateway\Finkarta\Response as FinkartaResponse;
class Request{
    protected $_url;
    protected $_data;
    protected $_file;
    protected $_dir ="c:/www/garan/test/fkarta/";
    public function __construct($d=[]){
        $this->_url = isset($d["url"])?$d["url"]:"https://testrequest.f-karta.ru/";
        $this->_data = isset($d["data"])?$d["data"]:[];
    }
    public function getUrl(){
        return $this->_url;
    }
    public function getRequestFile(){
        $this->build();
        return $this->_dir.$this->_file;
    }
    public function build(){
        $x = Garan24::obj2xml($this->_data);
        $file_name = "f-karta-request-".date('Y-m-d-His-u')."_req.xml";
        file_put_contents($file_name,$x->asXML());
        exec("\"c:\\www\\garan\\test\\fkarta\\stribog.exe\" -x \"" . $file_name . "\"", $output);
        $this->_file = "hashed_".$file_name;
        return file_get_contents($this->_file);
    }
    public function __toString(){
        return "URL:".$this->getUrl().Garan24::obj2xml($this->_params)->asXML();
    }
    public function buildResponse($res){
        $rs = new FinkartaResponse([
            "url"=>$this->_url,
            "request"=>$this->build(),
            "data" => $res
        ]);
        return $rs;
    }
};
?>
