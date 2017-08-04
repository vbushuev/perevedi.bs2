<?php
namespace Garan24;
use \Garan24\Object as GObject;
class Cacher extends GObject{
    public function __construct($a=[]){
        $this->_jdata["start"] = microtime(true);
        \Garan24 
        $this->_jdata["timer"] = isset($a["timer"])?$a["timer"]:15000;
        $this->_jdata["class"] = isset($a["class"])?$a["class"]:"\Garan24\Object";
        $this->_jdata["args"] = isset($a["args"])?$a["args"]:null;
        $this->_obj = $this->getInstance();
    }
    public function get(){
        $current = microtime(true);
        if(($current - $this->_start)<=$this->_timer)return $this->_obj;
        return $this->getInstance();
    }
    protected function getInstance(){
        $c = $this->class;
        $a = $this->args;
        return new $c($a);
    }
};
?>
