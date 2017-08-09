<?php
include("autoload.php");
use core\Config;

$pne = Config::payneteasy();
$aquere = $pne["env"];
$operation = $pne["operation"];

$host = "https://".$_SERVER["SERVER_NAME"];
$rq = Transaction::register([
    "amount"=>$_POST["amount"],
    "currency"=>$_POST["currency"],
    "client_ip"=>$_SERVER["REMOTE_ADDR"],
    "status"=>"0"
]);
// $ssn = "1490";//10001100 - 10001492
$ssn = "10001".rand(100,492);
$saleData = [
    "data"=>[
        "client_orderid" => $rq->id,
        "order_desc" => "Perevedi online",
        "first_name" => "Perevedi",
        "last_name" => "Online",
        "birthday" => "",
        "address1" => "Marshala Novikova str., 1",
        "address2" => "office 1307",
        "city" => "Moscow",
        "state" => "",//isset($data["state"])?$data["state"]:"",
        "zip_code" => "123098",
        "country" => "RU",
        "phone" => "+79265766710",
        "cell_phone" => "+79265766710",
        "amount" => $rq->amount,
        "currency" => $rq->currency,
        "email" => "vsb@garan24.ru",
        "ssn" => $ssn,
        "ipaddress" => $rq->client_ip,
        "site_url" => $host,
        "redirect_url" => $host."/response",
        "server_callback_url" =>  $host."/callback",
        //"merchant_data" => "VIP customer"
    ]
];
$saleData = array_merge($ariuspay[$aquere][$operation],$saleData);
// print_r($saleData);exit;
$request = new \Garan24\Gateway\Ariuspay\PreauthRequest($saleData);
switch($operation){
    case "CaptureRequest":$request = new \Garan24\Gateway\Ariuspay\CaptureRequest($saleData);break;
    case "SaleRequest":$request = new \Garan24\Gateway\Ariuspay\SaleRequest($saleData);break;
    case "TransferRequest":$request = new \Garan24\Gateway\Ariuspay\TransferRequest($saleData);break;
}
$connector = new \Garan24\Gateway\Ariuspay\Connector();
$connector->setRequest($request);
$connector->call();
$resp = $connector->getResponse();
$respArr = $resp->toArray();
$rq->update(["status"=>"1","order_id"=>$respArr["paynet-order-id"]]);
header("Content-Type: application/json;");
echo $resp->toJSON();
?>
