<?php
namespace Garan24;
use \Garan24\Garan24 as Garan24;
class HTTPConnector{
    /*******************************************************************************
     * Производит перенаправление пользователя на заданный адрес
     *
     * @param string $url адрес
     ******************************************************************************/
    public function redirect($url){
        Header("HTTP 302 Found");
        Header("Location: ".$url);
        die();
    }
    /*******************************************************************************
     * Совершает POST запрос с заданными данными по заданному адресу. В ответ
     * ожидается JSON
     *
     * @param string $url
     * @param array|null $data POST-данные
     *
     * @return array
     ******************************************************************************/
    public function post($url,$data = null){

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_FOLLOWLOCATION => true
        ];
        $curl = curl_init($url);
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        Garan24::debug("RAW RESPONSE:[{$response}]");
        return $response;
    }
    /*******************************************************************************
     * Совершает GET запрос с заданными данными по заданному адресу. В ответ
     * ожидается JSON
     *
     * @param string $url
     * @param array|null $data POST-данные
     *
     * @return array
     ******************************************************************************/
    public function get($url,$data = null){
        //$fp=fopen('../garan-curl-'.date("Y-m-d").'.log', 'wa');
        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => 1,
            //CURLOPT_STDERR => $fp,
            CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_FOLLOWLOCATION => true
        ];
        $urlparams = "";
        if(!is_null($data)&&is_array($data)){
            foreach($data as $k=>$v)$urlparams.=(strlen($urlparams)?"&":"").$k."=".$v;
        }
        $curl = curl_init($url.(preg_match("/\\\?/im",$url)?'&':'?').$urlparams);
        curl_setopt_array($curl, $curlOptions);
        $response = curl_exec($curl);
        Garan24::debug("RAW RESPONSE:[{$response}]");
        return $response;
    }
};
?>
