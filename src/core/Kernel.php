<?php
namespace core;
class Kernel{
    public $routing;
    public $templater;
    public function __construct(){
        $this->routing = new Routing;
        $this->templater = new Templater;
    }

};

?>
