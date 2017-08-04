<?php
namespace \Garan24\Store;
use \Garan24\Garan24 as GARAN24;
use \Garan24\Store\DBObject as Garan24dbObject;
use \Garan24\Store\Exception as StoreException;
class User extends Garan24dbObject{
    public function __construct($id){
        parent::__construct("{id:\"{$id}\"}");
        $this->sync();
    }
    protected function sync(){
        if(!isset($this->id))return;
        $this->execute("select * from ".$this->_dbdata["prefix"]."users where id = ".$this->id);
        $this->execute_meta("select * from ".$this->_dbdata["prefix"]."garan24_usermeta where user_id = ".$this->id);
    }
    public function __set($nc,$v){
        $n=strtolower($nc);
        parent::__set($nc,$v);
        if(in_array($n,["card-ref"])){
            ($this->exists("select 1 from ".$this->_dbdata["prefix"]."garan24_usermeta where user_id = ".$this->id." and VALUE_KEY = 'card-ref'"))?
                $this->prepare("update ".$this->_dbdata["prefix"]."garan24_usermeta set VALUE_DATA='".$v."' where user_id = ".$this->id." and VALUE_KEY = 'card-ref'")
                :$this->prepare("insert into ".$this->_dbdata["prefix"]."garan24_usermeta(USER_ID,VALUE_KEY,VALUE_DATA) values (".$this->id.",'card-ref','".$v."')");
        }
    }
};
?>
