<?php
include("autoload.php");
$data = file_get_contents('php://input');
$dataArr = [];
parse_str($data,$dataArr);
$r = [
    "url" => isset($_SERVER["HTTP_ORIGIN"])?$_SERVER["HTTP_ORIGIN"]:$_SERVER["HTTP_HOST"],
    "data" => $dataArr
];
$redirect_url = "";
try{
    $obj = new \Garan24\Gateway\Ariuspay\CallbackResponse($r,function($d){});
    if($obj->accept())$status = "accepted";
    else $status = "notaccepted";
}
catch(\Garan24\Gateway\Ariuspay\Exception $e){
    Log::error("Exception in AruisPay Response gateway:".$e->getMessage());
    $status = "notaccepted";
}
$schema = "https";
$host = $schema."://".$_SERVER["SERVER_NAME"];
header("Location: ".$host."?status=".$status);
?>
