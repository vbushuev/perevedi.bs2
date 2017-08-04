<?php
namespace core;
class Templater{
    public $viewFolder = '/views/';
    public function __construct(){
        $this->_params = Config::env();
    }
    public function header($header,$value){
        $this->_headers[$header] = $value;
        return $this;
    }
    public function cookie($header,$value){
        $this->_cookies[$header] = $value;
        return $this;
    }
    public function view($view,$args=[]){
        $viewPath = preg_replace('/\./m','/',$view);
        $viewFile = getcwd().$this->viewFolder.$viewPath.".php";
        // print_r([$viewFile,getcwd()]);exit;
        if(!file_exists($viewFile))throw new Exception("No view {$view} found");
        $this->_content = file_get_contents($viewFile);
        $params = array_merge($this->_params,$args);
        $this->_content = preg_replace_callback("/\{\{([^\}]+)\}\}/im",function($m)use($params){
            $key = trim($m[1]);
            return isset($params[$key])?$params[$key]:$key;
        },$this->_content);
        $this->_flush();
    }
    protected $_params = [];
    protected $_headers = [];
    protected $_cookies = [];
    protected $_content = '';
    protected function _flush(){
        echo $this->_content;
    }
};
?>
