<?php
use db\Model;
class Transaction extends Model{
    protected $timestampType = "timestamp";
    protected $fillable = ['amount','currency','status','email','client_ip','order_id'];
    public function __construct(){
        parent::__construct('requests','id','created_at','updated_at');
    }
    public static function register($a=[]){
        if(!count($a))return false;
        $res = new Transaction;
        try{
            $res->create($a);
        }
        catch(\Exception $e){
            $res = false;
        }
        return $res;
    }
};
?>
