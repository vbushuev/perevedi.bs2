<?php
namespace Garan24\Deal;
use \Garan24\Deal\WooRequiredObject as G24Object;
use \Garan24\Garan24 as Garan24;
class Item extends G24Object{
    public function __construct($a=[],$wc){
        $ii = is_array($a)?json_encode($a):$a;
        parent::__construct([
            "id",
            "product_id",
            "title",
            "description",
            "product_url",
            "product_img",
            "quantity",
            "weight",
            "dimensions",
            "original_price",
            "regular_price",
            "sale_price",
            "variations",
            "price",
            "images",
            "sku",
            "_links",
            "external_url"
        ],$ii,$wc);
        if(isset($this->dimensions)) $this->dimensions = new Dimensions($this->dimensions);
        if(isset($this->variations)) $this->variations = new Variations($this->variations);
    }
    public function sync(){
        $resource = new \WC_API_Client_Resource_Products($this->wc_client);
        $resp=null;
        try{
            try{$resp = $resource->get_by_sku($this->sku);}
            catch(\Exception $e){
                //echo json_encode($resp)." -- ". $e->getMessage();

            }
            if(is_null($resp))$resp = $resource->get($this->product_id);

            //$this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->product),true));
            $this->_jdata = array_merge(json_decode(json_encode($resp->product),true),$this->_jdata);
            $this->product_id = $resp->product->id;
            $this->id = $resp->product->id;
            $this->price = isset($this->_jdata["sale_price"])?$this->_jdata["sale_price"]:$this->_jdata["regular_price"];
        }
        catch(\WC_API_Client_Exception $e){
            $this->create();
        }
        catch(\Exception $e){
            echo json_encode($resp)." -- ". $e->getMessage();
        }

    }
    protected function create(){
        $item = [];
        $item["title"]=$this->_jdata["title"];
        $item["regular_price"]=$this->_jdata["original_price"];
        $item["sale_price"]=$this->_jdata["original_price"];

        $item["status"]="draft";
        $item["type"]="external";
        $item["images"]=[[
            'src'=>$this->_jdata["product_img"],
            'position'=>0
        ]];
        $item["external_url"] = $this->_jdata["product_url"];
        $item["sku"] = substr($this->_jdata["sku"],0,13);
        Garan24::debug("Insert item:". json_encode($item));
        $resp = $this->wc_client->products->create(["product"=> $item]);
        $this->product_id = $resp->product->id;
        //
        // $item = $this->_jdata;
        // $item["regular_price"]=$item["original_price"];
        // $item["sale_price"]=$item["original_price"];
        // $item["status"]="draft";
        // $item["type"]="external";
        // $item["images"]=[[
        //     'src'=>$item["product_img"],
        //     'position'=>0
        // ]];
        // $item["external_url"] = $item["product_url"];
        // //unset($item["sku"]);
        // $resp = $this->wc_client->products->create(["product"=> $item]);
        // $this->product_id = $resp->product->id;
    }
};
?>
