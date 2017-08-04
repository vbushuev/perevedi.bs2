<?php
namespace Garan24\Deal;
use \Garan24\RequiredObject as G24Object;
class Dimensions extends G24Object{
    public function __construct($a=[]){
        $ii = is_array($a)?json_encode($a):$a;
        parent::__construct([
            "height",
            "width",
            "depth"
        ],$ii);
    }
};
?>
