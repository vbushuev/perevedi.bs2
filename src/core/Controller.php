<?php
namespace core;
class Controller{
    public function __countruct(){
        $this->templater = new Templater;
    }
    protected $templater;
    protected function view($view,$args=[]){
        if(is_null($this->templater))$this->templater = new Templater;
        return $this->templater->view($view,$args);
    }
};
?>
