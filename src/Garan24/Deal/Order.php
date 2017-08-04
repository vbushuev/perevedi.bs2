<?php
namespace Garan24\Deal;
use \Garan24\Deal\WooRequiredObject as G24Object;
use \Garan24\Garan24 as Garan24;
class Order extends G24Object{
    public function __construct($a="{}",$wc){
        parent::__construct(
            ["id","order_id","order_url","order_total","order_currency","items","line_items","customer_id"],
            $a,
            $wc
        );
        if(isset($this->items)){
            $items = [];
            foreach($this->items as $item){
                $i = new Item($item,$wc);
                array_push($items, $i);
            }
            $this->items = $items;
        }
    }
    public function sync(){
        $resource = new \WC_API_Client_Resource_Orders($this->wc_client);
        $data = $this->_jdata;
        unset($data["items"]);
        $data["line_items"] = [];

        foreach($this->items as $item){
            $item->sync();
            array_push($data["line_items"],$item->toArray());
        }
        $resp=$resource->create($data);
        $this->id= $resp->order->id;

        $rrr = json_decode(json_encode($resp->order),true);
        Garan24::debug("Created order is:". json_encode($resp->order));
        $updateItems = [];
        foreach ($rrr["line_items"] as $li) {
            foreach($data["line_items"] as $ui){
                if($ui["product_id"]==$li["product_id"]){
                    $updateItems[] = [
                        "id" =>$li["id"],
                        "quantity"=>$ui["quantity"],
                        "product_id"=>$ui["product_id"],
                        "price"=>$ui["sale_price"],
                        "total"=>$ui["sale_price"]*$ui["quantity"],
                        "subtotal"=>$ui["sale_price"]*$ui["quantity"]
                    ];
                    break;
                }
            }
        }
        $updateOrder = [
            "line_items" => $updateItems
        ];

        $this->update($updateOrder);
    }
    public function get(){
        if(!isset($this->id)){
            return false;
        }
        $resource = new \WC_API_Client_Resource_Orders($this->wc_client);
        $resp = $resource->get($this->id);
        Garan24::debug("Getted order is:". json_encode($resp->order));
        $this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->order),true));
        if(isset($this->line_items)){
            $items = [];
            foreach($this->line_items as $item){
                $i = new Item($item,$this->wc_client);
                $i->sync();
                $i->sale_price = $item["price"];
                $i->regular_price = $item["price"];
                array_push($items, $i);
            }
            $this->items = $items;
        }
        $this->order_total = $this->_jdata["total"];
    }
    public function update($data){
        $resource = new \WC_API_Client_Resource_Orders($this->wc_client);
        Garan24::debug("Update order is:". json_encode($data));
        $resp = $resource->update($this->id,$data);
        $this->_jdata = array_merge($this->_jdata,json_decode(json_encode($resp->order),true));
        if(isset($this->line_items)){
            $items = [];
            foreach($this->line_items as $item){
                $i = new Item($item,$this->wc_client);
                $i->sync();
                $i->sale_price = $item["price"];
                $i->regular_price = $item["price"];
                array_push($items, $i);
            }
            $this->items = $items;
        }
    }
    public function getProducts(){
        if(!isset($this->items)) return [];
        $items = [];
        foreach($this->items as $item){
            $i = $item->toArray();
            array_push($items, $i);
        }
        return $items;
    }
};
?>
