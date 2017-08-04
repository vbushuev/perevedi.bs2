<?php
namespace core;
/**
 *
 */
class Notification{
    protected static $notifications = [];
    public static function __callStatic($n,$a){
        if(isset(self::$notifications[$n])){
            $notify = self::$notifications[$n];
            
        }
    }
}

?>
