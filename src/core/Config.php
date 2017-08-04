<?php
namespace core;
class Config{
    public static function __callStatic($n,$a){
        $cbConfig = self::_getConfigData();
        return isset($cbConfig[$n])?$cbConfig[$n]:false;
    }
    protected static function _getConfigData(){
        $cfgString = file_get_contents(".config");
        $result = [];
        if(preg_match_all("/^(.+)$/im",$cfgString,$ms)){

            foreach ($ms[1] as $matched) {
                if(!preg_match('/=/',$matched))continue;
                $kk = explode('=',$matched);
                $arrpath = trim($kk[0]);
                $value = trim($kk[1]);
                $pieces = explode('.', $arrpath);
                // print_r($pieces);
                // $current is a reference to the array in which new elements should be added
                $current = &$result;
                foreach($pieces as $key){
                    // add an empty array to the current array
                    if(!isset($current[ $key ])) $current[ $key ] = [];
                    // descend into the new array
                    $current = &$current[ $key ];
                }
                $current = $value;
            }

        }
        return $result;
    }
};
?>
