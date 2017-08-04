<?php
namespace Garan24\Deal;
use \Garan24\Deal\WooRequiredObject as G24Object;
use \Garan24\Store\DBConnector as DBConnector;
use \Garan24\Store\Exception as StoreException;
use \Garan24\Garan24 as Garan24;

class Customer extends G24Object{
    protected $db;
    public function __construct($a=[],$wc){
        $ii = is_array($a)?json_encode($a):$a;
        parent::__construct([
            "id",
            "customer_id",
            "email",
            "phone",
            "billing_address",
            "shipping_address"
        ],$ii,$wc);
        $this->db = new DBConnector();
        //$this->_jdata["phone"] = isset($this->_jdata["phone"])?preg_replace("/\+7/","7",$this->_jdata["phone"]):"";
    }
    public function sync(){
        $resource = new \WC_API_Client_Resource_Customers($this->wc_client);
        $resp=null;

        try{
            $this->get();
        }
        catch(\WC_API_Client_Exception $e){
            $this->create();
        }
        catch(\Exception $e){
            echo $resp;
        }
        try{
            $this->getCustomer();
        }
        catch(\Exception $e){
            $this->create();
        }
    }
    public function get(){
        $resp = $this->wc_client->customers->get($this->customer_id);
        if(isset($resp->customer))$this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->customer),true));
        $this->phone = $resp->customer->billing_address->phone;
    }
    public function update($data){
        if(isset($data["passport"])||isset($data["fio"]['middle'])||isset($data["fio"]['birthday'])){
            if(isset($data["passport"])){
                if(isset($data["passport"]["where"])) $data["passport"]["where"] = preg_replace(["/[\"\']+/m","/^\s+/","/\s+$/"],"",$data["passport"]["where"]);
                $d = preg_replace("/[\r\n]+/mi","",json_encode($data["passport"],JSON_UNESCAPED_UNICODE));
                //$d = json_encode($data["passport"],JSON_UNESCAPED_UNICODE);
                Garan24::debug("PASSPORT DATDA:[".$d."]");
                $this->_jdata["passport"] = $data["passport"];
                if($this->db->exists("select 1 from garan24_usermeta where user_id='{$this->customer_id}' and value_key='passport'")){
                    $this->db->update("update garan24_usermeta set value_data = '{$d}' where user_id='{$this->customer_id}' and value_key='passport'");
                }else{
                    $this->db->insert("insert into garan24_usermeta (user_id,value_key,value_data) values ('{$this->customer_id}','passport','{$d}')");
                }
            }
            if(isset($data["fio"]['middle'])){
                if($this->db->exists("select 1 from garan24_usermeta where user_id='{$this->customer_id}' and value_key='fio_middle'"))
                    $this->db->update("update garan24_usermeta set value_data = '{$data["fio"]['middle']}' where user_id='{$this->customer_id}' and value_key='fio_middle'");
                else $this->db->insert("insert into garan24_usermeta (user_id,value_key,value_data) values ('{$this->customer_id}','fio_middle','{$data["fio"]['middle']}')");
            }
            if(isset($data["fio"]['birthday'])){
                if($this->db->exists("select 1 from garan24_usermeta where user_id='{$this->customer_id}' and value_key='fio_birthday'"))
                    $this->db->update("update garan24_usermeta set value_data = '{$data["fio"]['birthday']}' where user_id='{$this->customer_id}' and value_key='fio_birthday'");
                else $this->db->insert("insert into garan24_usermeta (user_id,value_key,value_data) values ('{$this->customer_id}','fio_birthday','{$data["fio"]['birthday']}')");
            }
            return;
        }

        $resp = $this->wc_client->customers->update($this->id,$data);
        $this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->customer),true));
    }
    public function toAddressString(){
        $str = $this->billing_address['city']
            .", ".$this->billing_address['postcode']
            .", ".$this->billing_address['address_1'];
        return $str;
    }
    public function toNameString(){
        $str = $this->billing_address['first_name']
            ." ".$this->fio_middle;
        return $str;
    }
    public function toFullNameString(){
        $str = $this->billing_address['first_name']
            ." ".$this->_jdata['fio_middle']
            ." ".$this->billing_address['last_name'];
        return $str;
    }
    public function getDeals(){
        $sql = "select * ";
        $sql.= "from deals d ";
        $sql.= "where d.customer_id = ".$this->id;
        $sql.= " order by d.id desc";
        $deals = $this->db->select($sql);
        return $deals;
    }
    protected function create(){
        try{
            $resp = $this->wc_client->customers->get_by_email($this->email);
            Garan24::debug("Get_by_email .".$resp);
            if(isset($resp->customer)){
                $this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->customer),true));
                $this->customer_id = $this->id;
                //$this->update(['billing_address' => ['country' => 'RU','phone' => $this->phone]]);
                return;
            }
        }
        catch(\Exception $e){
            Garan24::debug("Get_by_email exception. ".$e->getMessage());
            try{
                $resp = $this->wc_client->customers->create(["customer"=> [
                    "email"=>$this->email,
                    "password"=>$this->phone,
                    "username"=>$this->email,
                    'billing_address' => [
                    //'billing' => [
                        'country' => 'RU',
                        'email' => $this->email,
                        'phone' => $this->phone
                    ]/*,
                    'billing' => [
                        'country' => 'RU',
                        'email' => $this->email,
                        'phone' => $this->phone
                    ]*/
                ]]);
                Garan24::debug("creating response. ");
                Garan24::debug($resp);
                $this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->customer),true));
                $this->customer_id = $this->id;
            }
            catch(\Exception $e){
                Garan24::debug("creating exception. ".$e->getMessage());
                $this->customer_id = 0;
            }
        }//$this->update([]);
    }
    protected function getCustomer(){
        $sql = "select u.id,u.user_email,um.meta_value as `phone`,u.id as `customer_id`";
        $sql.= " ,fio.value_data as `fio_middle`";
        $sql.= " ,bd.value_data as `fio_birthday`";
        $sql.= " ,passport.value_data as `passport`";
        $sql.= "from users u";
        $sql.= " join usermeta um on u.id = um.user_id and um.meta_key='billing_phone'";
        $sql.= " left outer join garan24_usermeta fio on u.id = fio.user_id and fio.value_key='fio_middle'";
        $sql.= " left outer join garan24_usermeta bd on u.id = bd.user_id and bd.value_key='fio_birthday'";
        $sql.= " left outer join garan24_usermeta passport on u.id = passport.user_id and passport.value_key='passport'";
        if(isset($this->email)&&isset($this->phone)){
            $sql.= " where u.user_email = '".$this->email."'";
            $sql.= " and um.meta_value = '".$this->phone."'";
        }
        elseif (isset($this->id)) {
            $sql.= " where u.id = '".$this->id."'";
        }
        Garan24::debug("get customer sql [".$sql."]");
        $user = $this->db->select($sql);
        $this->_jdata = array_merge($this->_jdata,$user);
        Garan24::debug("Customer passport: ".$this->_jdata["passport"]);
        $this->_jdata["passport"] = json_decode($this->_jdata["passport"],true);

    }
};
?>
