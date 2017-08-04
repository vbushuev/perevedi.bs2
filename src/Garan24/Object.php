<?php
namespace Garan24;
class Object {
    protected $_jdata=[];
    public function __construct($a="{}"){
        if(is_string($a)&&preg_match("/^\{.*\}$/m",$a))
            $this->parse($a);
        if(is_array($a))
            $this->_jdata=array_merge($this->_jdata,$a);
    }
    public function parse($a){
        if(!is_null($a)) $this->_jdata = array_change_key_case(json_decode($a,true),CASE_LOWER);
    }

    public function ___toString(){
        return $this->toJson();
    }
    public function toArray(){
        return $this->_jdata;
    }
    public function toJson(){
        return json_encode($this->_jdata);
    }
    public function __isset($nc){
        $n=strtolower($nc);
        //if(!isset($this->_jdata[$n])) throw new Exception("No such parameter \{{$n}\}");
        return isset($this->_jdata["{$n}"]);
    }
    public function __unset($nc){
        $n=strtolower($nc);
        unset($this->_jdata["{$n}"]);
    }
    public function __get($nc){
        $n=strtolower($nc);
        //if throw new Exception("No such parameter \{{$nc}\}");
        return ($this->__isset($n))?$this->_jdata["{$n}"]:"";
    }
    public function __set($nc,$v){
        $n=strtolower($nc);
        $this->_jdata["{$n}"]=$v;
    }
};
?>
