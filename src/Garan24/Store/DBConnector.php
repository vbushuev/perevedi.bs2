<?php
namespace Garan24\Store;
use \Garan24\Garan24 as GARAN24;
use \Garan24\Object as Garan24Object;
use \Garan24\Store\Exception as StoreException;
class DBConnector{
    protected $_dbdata = [];
    public function __construct(){
        $this->_dbdata["host"]=GARAN24::$DB["host"];
        $this->_dbdata["user"]=GARAN24::$DB["user"];
        $this->_dbdata["pass"]=GARAN24::$DB["pass"];
        $this->_dbdata["schema"]=GARAN24::$DB["schema"];
        $this->_dbdata["prefix"]=GARAN24::$DB["prefix"];
        $this->_dbdata["connected"]=false;
        $this->_dbdata["conn"]=null;
    }
    public function __destruct(){
        if($this->_dbdata["connected"]) $this->_dbdata["conn"]->close();
    }
    protected function connect(){
        $this->_dbdata["conn"] = new \mysqli($this->_dbdata["host"],$this->_dbdata["user"],$this->_dbdata["pass"],$this->_dbdata["schema"]);
        //GARAN24::debug($this->_dbdata);
        if($this->_dbdata["conn"]->connect_errno) throw new StoreException("No db connection. Error:".$this->_dbdata["conn"]->connect_error);
        $this->_dbdata["connected"] = true;
        $this->_dbdata["conn"]->set_charset('utf8');
    }
    protected function prepare($sql){
        if(!$this->_dbdata["connected"]) $this->connect();
        $sql = $this->_prefixed($sql);
        //$result = $this->_dbdata["conn"]->query($sql,MYSQLI_USE_RESULT);
        $result = $this->_dbdata["conn"]->query($sql);
        if(!$result) throw new StoreException("Fail to execute {$sql}. Error:".$this->_dbdata["conn"]->error);
        return $result;
    }
    protected function _prefixed($sql){
        $r = $sql;
        $r = preg_replace("/(from|join|into|update)\s+([a-z0-9_]+)/im","$1 ".$this->_dbdata["prefix"]."$2",$r);
        //$r = preg_replace("/join\s+([a-z0-9_]+)/im","join ".$this->_dbdata["prefix"]."$1",$r);
        //$r = preg_replace("/into\s+([a-z0-9_]+)/im","into ".$this->_dbdata["prefix"]."$1",$r);
        //$r = preg_replace("/update\s+([a-z0-9_]+)/im","update ".$this->_dbdata["prefix"]."$1",$r);
        return $r;
    }
    public function select($sql){
        $result = $this->prepare($sql);
        if(!$result->num_rows) throw new StoreException("Sync failed no data is retrieved.");
        $ret = $result->fetch_array(MYSQLI_ASSOC);
        $result->close();
        return $ret;
    }
    public function selectAll($sql){
        $result = $this->prepare($sql);
        for ($ret = []; $tmp = $result->fetch_array(MYSQLI_ASSOC);) $ret[] = $tmp;
        $result->close();
        return $ret;
    }
    public function insert($sql){
        if(!$this->_dbdata["connected"]) $this->connect();
        $sql = $this->_prefixed($sql);
        if(!$this->_dbdata["conn"]->query($sql)){
            throw new StoreException($sql." execution error: "
                .$this->_dbdata["conn"]->error);
        }
    }
    public function update($sql){
        $this->insert($sql);
    }
    public function exists($sql){
        $result = $this->prepare($sql);
        return ($result->num_rows>0);
    }
};
?>
