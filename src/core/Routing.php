<?php
namespace core;
use http\Request;

class Routing{
    public static function route($route=false){
        $class = "Controller";
        $method = "index";
        $arguments = [];
        while(true){
            $route = ($route===false)?$_SERVER["REQUEST_URI"]:$route;
            $pi=parse_url($route);
            $path = strtolower(preg_replace('/^\//','',$pi['path']));
            if(trim($path)==""){
                $class="Home".$class;
            }
            $expath = preg_split('/\//',$path);
            $class = "controllers\\".(count($expath)?ucfirst(mb_strtolower($expath[0])):"Home").$class;
            $method = (count($expath)>1)?$expath[1]:"index";
            if(count($expath)>2)$arguments = array_slice($expath,2);
            break;
        }
        if(!class_exists($class))throw new Exception("Class {$class} doesn't exists ");
        $controller = new $class;
        if( !($controller instanceof Controller) ) throw new Exception("Controller for route: {$route} doesn't found");
        if ( !method_exists($controller,$method)) throw new Exception("No route for {$route} is defined");
        $controller->$method(...$arguments);
    }
};
?>
