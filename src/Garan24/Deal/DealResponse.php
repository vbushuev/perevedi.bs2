<?php
namespace Garan24\Deal;
use \Garan24\RequiredObject as G24Object;
class DealResponse extends G24Object{
    protected $shop;
    public function __construct($a="{}"){
        parent::__construct([
            "id",
            "redirect_url",
            "code",
            "error",
            "order"
        ],$a);
    }
};
?>
