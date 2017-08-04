<?php
namespace Garan24\Delivery\BoxBerry;
class BoxBerry extends \Garan24\HTTPConnector{
    protected $token;
    protected $pvz;
    public function __construct(){
        // $this->token = '17324.prpqcdcf'; //eurolego
        $this->token = '18455.rvpqeafa'; //creative spaces
        //$this->pvz = '77961'; // Moscow
        //$this->pvz = 'B70 0BD'; //  - Англия
        $this->pvz = '2983 GR'; //  Голландия
    }
    public function __call($f,$a){
        $m = $this->validateFunction($f);
        $url = "https://api.boxberry.de/json.php";
        if($m=="get"){
            $url.="?token=".$this->token."&method=".$f;
            return $this->get($url,(count($a)?$a[0]:null));
        }
        $a["token"] = $this->token;
        $a["method"] = $f;
        return $this->post($url,(count($a)?$a[0]:null));
    }
    protected function validateFunction($f){
        $avaliable = [
            "ParselCreate"=>"post",
            "ParcelCreateForeign"=>"post",
            "ParselCheck"=>"get",
            "ParselList"=>"get",
            "ParselDel"=>"get",
            "ParselStory"=>"get",
            "ParselSend"=>"get",
            "ParselSendStory"=>"get",
            "OrdersBalance"=>"get",

            "ListCities"=>"get",
            "ZipCheck"=>"get",
            "ListPoints"=>"get",
            "ListZips"=>"get",
            "ListStatusesFull"=>"get",
            "ListServices"=>"get",
            "CourierListCities"=>"get",
            "DeliveryCosts"=>"get",
            "PointsByPostCode"=>"get",
            "PointsDescription"=>"get",

            "DeliveryCostsF"=>"get"
        ];
        if(!isset($avaliable[$f])) throw new Exception("No such service or function in BoxBerry:{".$f."}");
        return $avaliable[$f];
    }
};

?>
