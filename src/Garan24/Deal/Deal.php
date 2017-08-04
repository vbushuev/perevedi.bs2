<?php
namespace Garan24\Deal;
use \Garan24\Deal\Customer as Customer;
use \Garan24\Deal\WooRequiredObject as G24Object;
use \Garan24\Store\DBConnector as DBConnector;
use \Garan24\Store\Exception as StoreException;
use \Garan24\Garan24 as Garan24;

class Deal extends G24Object{
    protected $_loaded = false;
    protected $shop = false;
    protected $customer = false;
    protected $db;
    protected $redirect_url = "https://checkout.gauzymall.com";
    protected $deal = false;
    protected $raw_request="";
    public function __construct($a=[]){
        parent::__construct([
            "x_secret",
            "x_key",
            "version",
            "response_url",
            "payment",
            "delivery",
            "order",
            "currency"
        ]);
        $this->db = new DBConnector();
        if(is_array($a)&&count($a)){
            if(isset($a["id"])){
                $this->byId($a["id"]);
                if(isset($a["data"])){
                    $this->update($a["data"]);
                }
            }
        }

    }
    public function sync(){
        $ret = new DealResponse();
        try{
            $this->getKeyAndSecret();
            $this->getShop();
        }
        catch(Exception $e){
            $ret->code = 500;
            $ret->error = "Wrong secret or key value. Or auth data is expired.";
        }
        try{
            $this->order->customer_id = $this->shop["user_id"];
            $this->order->sync();
        }
        catch(Exception $e){
            $ret->code = 500;
            $ret->error = "Wrong order parameters.";
        }
        try{
            $this->createDeal();
            $ret->id = $this->order->id;
            $ret->code = 0;
            $ret->error = 0;
            $ret->redirect_url = "https://checkout.gauzymall.com/?id=".$this->order->id;
        }
        catch(Exception $e){
            $ret->code = 500;
            $ret->error = "No deal registered.";
        }
        return $ret;
    }
    public function finish(){
        try{
            $this->order->update(['status' => 'processing']);
        }
        catch (\Exception $e){
            Garan24::debug($e);
        }
        $ret = new DealResponse();
        $ret->id = $this->deal["internal_order_id"];
        $ret->code = 0;
        $ret->error = 0;
        $ret->order = [
            "order_id" => $this->deal["external_order_id"],
            "status"=>"onconfirm",
            "shipping"=>$this->customer->shipping_address
        ];
        $this->update(["status"=>'checkout']);
        return $ret;
    }
    public function byJson($a){
        $this->raw_request = $a;
        $a = is_array($a)?json_encode($a):$a;
        $this->_jdata = array_change_key_case(json_decode($a,true),CASE_LOWER);
        $this->getKeyAndSecret();
        $this->initWC($this->x_key,$this->x_secret,$this->woo_api_domain);
        $this->order = new Order($this->order,$this->wc_client);
        $this->_loaded = true;
    }
    public function byId($id){
        $sql = "select d.id,d.shop_id,d.internal_order_id,d.external_order_id,s.consumer_key,wak.consumer_secret,d.response_url,d.status";
        $sql.= ",d.payment_id as payment_type_id,d.delivery_id as delivery_type_id ";
        $sql.= ",dt.name as delivery_type_name,dt.desc as delivery_type_desc ";
        $sql.= ",pt.name as payment_type_name,pt.desc as payment_type_desc ";
        $sql.= ",d.shipping_cost as `shipping_cost`";
        $sql.= ",ds.status as `status`";
        $sql.= ",d.service_fee";
        $sql.= ",s.test as `istest`";
        $sql.= ",d.currency as `currency`";
        $sql.= " from deals d ";
        $sql.= " join shops s on s.id=d.shop_id";
        $sql.= " join woocommerce_api_keys wak on wak.key_id = s.api_key_id";
        $sql.= " join garan24_deal_statuses ds on ds.id = d.status";
        $sql.= " left outer join garan24_deliverytype dt on dt.id = d.delivery_id";
        $sql.= " left outer join garan24_paymenttype pt on pt.id = d.payment_id";
        $sql.= " where d.internal_order_id =".$id;
        try{
            $this->deal = $this->db->select($sql);
            $this->_jdata = array_merge($this->_jdata,$this->deal);
            $this->x_key = $this->deal["consumer_key"];
            $this->x_secret = $this->deal["consumer_secret"];
            $this->response_url = $this->deal["response_url"];
            $this->payment = ["id"=>$this->deal["payment_type_id"],"name"=>$this->deal["payment_type_name"],"desc"=>$this->deal["payment_type_desc"] ];
            $this->delivery = ["id"=>$this->deal["delivery_type_id"],"name"=>$this->deal["delivery_type_name"],"desc"=>$this->deal["delivery_type_desc"] ];
            $this->_loaded = true;
            Garan24::debug("Deal is : ". json_encode($this->_jdata));
            $this->getShop();
            $this->getOrder($id);
            $this->getCustomer();
            return true;
        }
        catch(\Exception $e){
            Garan24::debug("Deal [#{$id}] not found.".$e->getMessage());
            return false;
        }
        return false;
    }
    public function update($data){
        if(!$this->_loaded)return;
        if(isset($data["customer_id"])){
            $sql = "update deals set customer_id = '".$data["customer_id"]."' where id=".$this->deal["id"];
            $this->db->update($sql);
            $this->order->update(["customer_id"=>$data["customer_id"]]);
        }
        if (isset($data["payment_id"])) {
            $sql = "update deals set payment_id = '".$data["payment_id"]."' where id=".$this->deal["id"];
            $this->db->update($sql);
            $this->payment = $this->db->select("select pt.id,pt.name,pt.desc from garan24_paymenttype pt where id=".$data["payment_id"]);
        }
        if (isset($data["delivery_id"])) {
            $sql = "update deals set delivery_id = '".$data["delivery_id"]."' where id=".$this->deal["id"];
            $this->db->update($sql);
            $this->delivery = $this->db->select("select pt.id,pt.name,pt.desc from garan24_deliverytype pt where id=".$data["delivery_id"]);
        }
        if( isset($data['fio'])){
            $this->customer->update([
                "last_name"=>$data['fio']['last'],
                "first_name"=>$data['fio']['first'],
                "billing_address" => [
                    "last_name"=>$data['fio']['last'],
                    "first_name"=>$data['fio']['first'],
                ],
                "shiping_address" => [
                    "last_name"=>$data['fio']['last'],
                    "first_name"=>$data['fio']['first'],
                ]
            ]);
        }
        if(isset($data['passport'])){
            $this->customer->update($data);
        }
        if( isset($data["billing"]) ){
            $data["billing"]["phone"] = $this->customer->phone;
            $addr = $data["billing"];
            $cust = [];
            if(isset($data['fio'])&&isset($data['fio']['last'])){
                $addr["last_name"] = $data['fio']['last'];
                $cust["last_name"] = $data['fio']['last'];
            }
            if(isset($data['fio'])&&isset($data['fio']['first'])){
                $addr["first_name"] = $data['fio']['first'];
                $cust["first_name"] = $data['fio']['first'];
            }
            if(count($cust)){
                $this->customer->update($cust);
            }
            $this->order->update([
                "shipping_address"=>$addr,
                "shipping_lines"=>[]
            ]);
            $this->customer->update([
                "billing_address" => $addr,
                "shiping_address" => $addr
            ]);
        }
        if(isset($data["card-ref-id"])){
            try{
                $sql = "select id as card_ref_id from garan24_cardrefs where card_ref_id = '".$data["card-ref-id"]."'";
                $d = $this->db->select($sql);
                $sql = "insert into garan24_user_cardref (user_id,card_ref_id,deal_id) values(".$this->customer->customer_id.",".$d["card_ref_id"].",".$this->deal["id"].")";
                $this->db->insert($sql);
            }
            catch(\Exception $e){
                $sql = "insert into garan24_cardrefs (card_ref_id) values('".$data["card-ref-id"]."')";
                $this->db->insert($sql);
                $sql = "insert into garan24_user_cardref (user_id,card_ref_id,deal_id) values(".$this->customer->customer_id.",last_insert_id(),".$this->deal["id"].")";
                $this->db->insert($sql);
            }
        }
        if(isset($data["shipping_cost"])){
            $sql = "update deals set shipping_cost='".$data["shipping_cost"]."' where id = ".$this->deal["id"];
            $this->db->update($sql);
            $this->_jdata["shipping_cost"] = $data["shipping_cost"];
        }
        if(isset($data["status"])){
            $sql = "update deals set status=(select id from garan24_deal_statuses where status='".$data["status"]."') where id = ".$this->deal["id"];
            $this->db->update($sql);
        }
        if(isset($data["service-fee"])){
            $sql = "update deals set service_fee=".$data["service-fee"]." where id = ".$this->deal["id"];
            $this->db->update($sql);
        }
    }
    public function getCustomer(){
        if(!$this->_loaded)return;
        if(($this->customer!==false)&&is_object($this->customer)&&($this->customer instanceof Customer)) return $this->customer;
        $this->customer = new Customer('{"id":"'.$this->order->customer_id.'","customer_id":"'.$this->order->customer_id.'"}',$this->wc_client);
        $this->customer->sync();
        Garan24::debug("Customer is : ". json_encode($this->customer->toArray()));
        return $this->customer;
    }
    public function getShopUrl(){
        if(!$this->_loaded)return;
        return $this->shop["link"];
    }
    public function getPaymentTypes(){
        if(!$this->_loaded)return;
        //if(count($this->payments))return $this->payments;
        $sql = "select pt.id,pt.code,pt.name,pt.desc";
        $sql.= " from garan24_paymenttype pt";
	    $sql.= " join garan24_shop_payments sp on sp.payment_id=pt.id";
        $sql.= " where sp.shop_id=".$this->shop["id"];
        if(isset($this->_jdata["payments"])&&count($this->_jdata["payments"])){
            $sql.= " and pt.code in ('".join("','",$this->_jdata["payments"])."')";
        }
        $sql.= " order by pt.id";
        $payments=[];
        Garan24::debug("getPaymentTypes sql query:".$sql);
        try{$payments = $this->db->selectAll($sql);}
        catch(\Exception $e){
            Garan24::debug("getPaymentTypes exception : ". $e);
        }
        return $payments;
    }
    public function getDeliveryTypes(){
        if(!$this->_loaded)return;
        //if(count($this->deliveries))return $this->deliveries;
        $deliveries=[];
        $sql = "select dt.id,dt.code,convert(dt.name using utf8) as name,convert(dt.desc using utf8) COLLATE utf8_bin as 'desc',dt.price,dt.timelaps";
        $sql.= " from garan24_deliverytype dt";
	    $sql.= " join garan24_shop_delivery sd on sd.delivery_id=dt.id";
        $sql.= " where sd.shop_id=".$this->shop["id"];
        if(isset($this->_jdata["deliveries"])&&count($this->_jdata["deliveries"])){
            $sql.= " and dt.code in ('".join("','",$this->_jdata["deliveries"])."')";
        }
        $sql.= " order by dt.id";
        Garan24::debug("getDeliveryTypes sql query:".$sql);
        try{$deliveries = $this->db->selectAll($sql);}
        catch(\Exception $e){
            Garan24::debug("getDeliveryTypes exception : ". $e);
        }
        return $deliveries;
    }
    public function __caller($f,$a){
        if(is_array($a)&&count($a)){
            $val = (count($a)==1)?$a[0]:$a;
            if(isset($this->deal[$f]))$this->deal[$f] = $val;
            elseif(isset($this->$f))$this->$f =$val;
            elseif(isset($this->_jdata[$f]))$this->_jdata[$f]=$val;
            return;
        }
        if(isset($this->deal[$f]))return $this->deal[$f];
        if(isset($this->$f))return $this->$f;
        if(isset($this->_jdata[$f]))return $this->_jdata[$f];
    }
    protected function getShop(){
        if(!$this->_loaded)return;
        if($this->shop!==false) return;
        $sql = "select s.id,s.name,s.link,s.description,s.api_key_id,wak.user_id,s.test as istest from woocommerce_api_keys wak";
        $sql.= " join shops s on s.api_key_id = wak.key_id";
        $sql.= " where wak.consumer_secret = '".$this->x_secret."'";
        $this->shop = $this->db->select($sql);
        Garan24::debug("Shop is : ". json_encode($this->shop));
    }
    protected function createDeal(){
        if(!$this->_loaded)return;
        $sql = "insert into deals (amount,currency,shop_id,status,internal_order_id,external_order_id,external_order_url,customer_id,response_url,payments,deliveries,raw_request) ";
        $sql.= "values(";
        $sql.= $this->order->order_total;
        $sql.= ",'".$this->order->order_currency."'";
        $sql.= ",'".$this->shop["id"]."'";
        $sql.= ",'1'";
        $sql.= ",'".$this->order->id."'";
        $sql.= ",'".$this->order->order_id."'";
        $sql.= ",'".$this->order->order_url."'";
        $sql.= ",'".$this->order->customer_id."'";
        $sql.= ",'".$this->response_url."'";
        $sql.= ",'".(isset($this->payment)?$this->payment:"")."'";
        $sql.= ",'".(isset($this->delivery)?$this->delivery:"")."'";
        $sql.= ",'".$this->raw_request."'";
        $sql.= ")";
        $this->db->insert($sql);
    }
    protected function getOrder($id){
        if(!$this->_loaded)return;
        if(($this->order!==false)&&is_object($this->order)&&($this->Order instanceof Order))return;
        $this->initWC($this->x_key,$this->x_secret,"https://www.gauzymall.com");
        $this->order = new Order('{"id":"'.$id.'"}',$this->wc_client);
        $this->order->get();
    }
    protected function getKeyAndSecret(){
        $this->_jdata["woo_api_domain"] = 'https://www.gauzymall.com';
        if(!isset($this->domain_id)) return false;
        $sql = "select s.consumer_key,wak.consumer_secret, s.name ,s.test as `istest`";
        $sql.= "from shops s";
        $sql.= " join woocommerce_api_keys wak on wak.key_id = s.api_key_id";
        $sql.= " where s.id =".$this->domain_id;
        try{
            $this->deal = $this->db->select($sql);
            $this->x_key = $this->deal["consumer_key"];
            $this->x_secret = $this->deal["consumer_secret"];
            //$this->_jdata["woo_api_domain"] = ($this->deal["istest"]==1)?'http://xrayshopping.ru':'http://gauzymall.com';
            return true;
        }
        catch(\Exception $e){
            Garan24::debug("No key [#{$this->doain_id}] found.".$e->getMessage());
            return false;
        }
        return false;
    }
};
?>
