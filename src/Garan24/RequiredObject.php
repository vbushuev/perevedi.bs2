<?php
namespace Garan24;
class RequiredObject extends Object {
    protected $_rdata=[];
    public function __construct($r,$a="{}"){
        $this->_rdata = $r;
        $ii = is_array($a)?json_encode($a):$a;
        parent::__construct($ii);
    }
    public function __isset($nc){
        $n=strtolower($nc);
        //if(!in_array($n,$this->_rdata))return false;
        return isset($this->_jdata["{$n}"]);
    }
    public function __set($nc,$v){
        $n=strtolower($nc);
        //if(!in_array($n,$this->_rdata)) throw new Exception("No such parameter \{{$n}\}");
        $this->_jdata["{$n}"]=$v;
    }
    public function check($useException = false){
        foreach($this->_rdata as $k){
            $ret = true;
            if(!$this->__isset($k)) $ret = false;;
            $o = $this->_jdata["{$k}"];
            if(is_string($o)&&empty($o))$ret = false;
            if(is_array($o)&&!count($o))$ret = false;
            if(is_object($o) && $o instanceof RequiredObject && $o->isempty()) $ret = false;
            if(is_object($o) && is_null($o)) $ret = false;
            if(!$ret){
                if($useException)throw new Exception("Required [{$k}] is not setted or empty");
                else return false;
            }
        }
        return true;
    }
    public function isempty(){
        return (count($this->_jdata)>0)?false:true;
    }
    public function __toString(){
        return json_encode($this->toArray(),JSON_PRETTY_PRINT);
    }
    public function toArray(){
        $out = $this->_jdata;
        foreach($out as $k=>$v){
            if(is_array($v)){
                $a = [];
                foreach ($v as $n=>$value) {
                    if(is_object($value)&& $value instanceof RequiredObject){
                        array_push($a,$value->toArray());
                    }
                    else $a[$n]=$value;
                }
                $out[$k]=$a;
            }
            else if(is_object($v)&& $v instanceof RequiredObject){
                $out[$k] = $v->toArray();
            }
        }
        return $out;
    }
};
?>
