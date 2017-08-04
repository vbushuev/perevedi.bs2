<?php
namespace Garan24\Deal;
use \Garan24\Garan24 as Garan24;
use \WC_API_Client as WC_API_Client;
use \WC_API_Client_Exception as WC_API_Client_Exception;
use \WC_API_Client_HTTP_Exception as WC_API_Client_HTTP_Exception;
class WooRequiredObject extends \Garan24\RequiredObject {
    protected $wc_client=null;
    public function __construct($r,$a="{}",$wc=null){
        parent::__construct($r,$a);
        $this->wc_client = is_null($wc)?$this->wc_client:$wc;
    }
    public function getWc(){
        return $this->wc_client;
    }
    protected function initWC($key,$secret,$dom = "https://garan24.ru"){
        $domain = $dom;
        $consumer_key = $key;//"ck_8ff71be2b15d1dddbe939fb30e7fd0dfc6419ca2";
        $consumer_secret = $secret;//"cs_735d73f347e10723402539ac503a9df8413f6287";
        $options = [
            'debug'           => true,
        	'return_as_array' => false,
        	'validate_url'    => false,
        	'timeout'         => 30,
            'ssl_verify'      => false
        ];
        try {
            $this->wc_client = new WC_API_Client( $domain, $consumer_key,$consumer_secret, $options );
        }
        catch ( Exception $e ) {
            Garan24::debug("NOT Connected to WC");
            $resp["code"] = $e->getCode();
            $resp["message"] = $e->getMessage();
            if ( $e instanceof WC_API_Client_HTTP_Exception ) {
                $resp["request"] = $e->get_request();
                $resp["response"] = $e->get_response();
            }
        }
    }
};
?>
