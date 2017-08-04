<?php
namespace db;
use core\Log;
use core\Config;
use core\Common as Common;
use db\Connector as dbConnector;
class Model extends Common{
    protected $connection = "db";
    protected $table=false;
    protected $primary;
    protected $created_at;
    protected $updated_at;
    protected $idx = false;
    protected $conn=null;
    protected $data=false;
    protected $fillable=[];
    protected $timestampType = "unixtime";
    //public function __construct($t,$idxField="id",$created_at=false,$updated_at=false){
    public function __construct($t,$idxField=false,$created_at=false,$updated_at=false){
        $this->table=$t;
        $this->primary=$idxField;
        $this->created_at=$created_at;
        $this->updated_at=$updated_at;
        $this->conn = new dbConnector();
        if(!count($this->fillable)){
            $sql = "select * from ".$this->table." where 1 limit 1";
            $r = $this->conn->select($sql);
            if(count($r))$this->fillable = array_keys($r);
            $removeFields=[];
            if($this->primary!==false)$removeFields[]= $this->primary;
            if($this->created_at!==false)$removeFields[]=$this->created_at;
            if($this->updated_at!==false)$removeFields[]=$this->updated_at;
            $this->fillable=array_diff($this->fillable,$removeFields);
        }
        $this->conn->disconnect();
    }
    public static function find($a=[]){
        $class = get_called_class();
        $res = new $class;
        try{
            $res->getOne($a);
        }
        catch(\Exception $e){
            $res = false;
        }
        return $res;
    }
    public function getOne($a=[]){
        $sql = "select * from ".$this->table." where ";
        $params = [];
        if(is_array($a))foreach($a as $f=>$v){
            if(in_array($f,$this->fillable) || $f==$this->primary || $f== $this->updated_at || $f== $this->created_at ) {
                $val = $v;
                if(!preg_match("/^\s*[\!=><]|(like)/",$v))$val = " = '".$v."'";
                $params[] = "{$f} {$val}";
            }
        }
        else $params[]=$this->primary." = ".$a;
        $sql.=join(" and ",$params);
        $sql.=" limit 1";
        $r=[];
        //Log::debug("find",$sql);
        try{$r = $this->conn->select($sql);}
        catch(\Exception $e){
            //Log::error($e);
            throw $e;
        }
        $this->conn->disconnect();
        if(!count($r))throw new Exception("not found");
        foreach ($r as $key => $value) {
            if($key==$this->primary){
                $this->idx = $value;
                break;
            }
        }
        $this->publicData = $r;
        return $this;
    }
    public function findOrCreate($a=null){
        $a = is_null($a)?$this->publicData:$a;
        try{
            $this->find($a);
        }
        catch(\Exception $e){
            $this->create($a);
        }
        return $this;
    }
    public function update($a=[]){
        if($this->idx===false)return $this;
        $this->publicData = $a;
        $sql = "update {$this->table} set ";
        $params = [];
        if($this->updated_at!==false)$params[] = "{$this->updated_at} = '".$this->currentTimestamp()."'";
        if(is_array($a))foreach($a as $f=>$v){
            if(in_array($f,$this->fillable)) $params[] = "{$f}='{$v}'";
        }
        $sql.=join(", ",$params);
        $sql.=" where ".$this->primary." = ".$this->idx;
        $r = $this->conn->update($sql);
        $this->conn->disconnect();

        return $this;
    }
    public function create($ins=[]){
        if(!count($ins))return $this;
        if($this->primary!==false && isset($ins[$this->primary]))unset($ins[$this->primary]);
        $a = array_intersect_key($ins,array_flip($this->fillable));
        //print_r($a);exit;
        if($this->created_at!==false)$a[$this->created_at] = $this->currentTimestamp();
        if($this->updated_at!==false)$a[$this->updated_at] = $this->currentTimestamp();
        $sql = "insert into {$this->table}(`".join("`,`",array_keys($a))."`) values ('".join("','",array_values($a))."');";
        //Log::debug($sql);
        $r = $this->conn->insert($sql);
        $this->conn->disconnect();
        if($r!==true){
            $this->idx = $r;
            $a[$this->primary]=$this->idx;
        }
        $this->publicData = $a;
        return $this;
    }
    public function delete($a =[]){
        if(!is_array($a) && !count($a))return;
        $sql = "delete from ".$this->table." where ";
        $params = [];
        if(is_array($a))foreach($a as $f=>$v){
            if(in_array($f,$this->fillable) || $f==$this->primary || $f== $this->updated_at || $f== $this->created_at ) {
                $val = $v;
                if(!preg_match("/^\s*[\!=><]|(like)/",$v))$val = " = '".$v."'";
                $params[] = "{$f} {$val}";
            }
        }
        $sql.=join(" and ",$params);
        try{$r = $this->conn->delete($sql);}
        catch(\Exception $e){
            //Log::error($e);
            throw $e;
        }
        $this->conn->disconnect();
        return;
    }
    public function save(){
        return ($this->idx===false)?$this->create($this->publicData):$this->update($this->publicData);
    }
    public function count(){
        try{$r = $this->conn->select("select count(*) as quantity from ".$this->table);}
        catch(\Exception $e){
            Log::error($e);
            throw $e;
        }
        $this->conn->disconnect();
        if(!count($r))throw new Exception("not found");
        return intval($r["quantity"]);
    }
    public function max($field){
        try{$r = $this->conn->select("select max(".$field.") as result from ".$this->table);}
        catch(\Exception $e){
            Log::error($e);
            throw $e;
        }
        $this->conn->disconnect();
        if(!count($r))throw new Exception("not found");
        return $r["result"];
    }
    public function get($a=[]){
        $sql = "select * from ".$this->table." where ";
        $params = [];
        if(is_array($a))foreach($a as $f=>$v){
            if(in_array($f,$this->fillable) || $f==$this->primary || $f== $this->updated_at || $f== $this->created_at ) {
                $val = $v;
                if(!preg_match("/^\s*[\!=><]|(like)/",$v))$val = " = '".$v."'";
                $params[] = "{$f} {$val}";
            }
        }
        else $params[]=$this->primary." = ".$a;
        $sql.=join(" and ",$params);
        $r=[];
        //Log::debug("find",$sql);
        try{$r = $this->conn->selectAll($sql);}
        catch(\Exception $e){
            //Log::error($e);
            throw $e;
        }
        $this->conn->disconnect();
        if(!count($r))throw new \Exception("not found");
        $this->publicData = $r[0];
        foreach ($this->publicData as $key => $value) {
            if($key==$this->primary){
                $this->idx = $value;
                break;
            }
        }
        return $r;
    }
    protected function currentTimestamp(){
        switch($this->timestampType){
            case "timestamp":return date("Y-m-d H:i:s");
            default: return time();
        }
    }
};
?>
