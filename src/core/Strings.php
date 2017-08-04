<?php
namespace core;
class Strings{
    public static function transcript($s){
        $reps = [
            "/а/imu" =>"a",
            "/б/imu" =>"b",
            "/в/imu" =>"v",
            "/г/imu" =>"g",
            "/д/imu" =>"d",
            "/е/imu" =>"e",
            "/ж/imu" =>"dj",
            "/з/imu" =>"z",
            "/и/imu" =>"i",
            "/й/imu" =>"y",
            "/к/imu" =>"k",
            "/л/imu" =>"l",
            "/м/imu" =>"m",
            "/н/imu" =>"n",
            "/о/imu" =>"o",
            "/п/imu" =>"p",
            "/р/imu" =>"r",
            "/с/imu" =>"s",
            "/т/imu" =>"t",
            "/у/imu" =>"u",
            "/ф/imu" =>"f",
            "/х/imu" =>"h",
            "/ч/imu" =>"ch",
            "/щ/imu" =>"sch",
            "/ш/imu" =>"sh",
            "/ь/imu" =>"_",
            "/ц/imu" =>"ts",
            "/ъ/imu" =>"_",
            "/э/imu" =>"e",
            "/ю/imu" =>"u",
            "/я/imu" =>"ya",
            "/ы/imu" =>"y",
            "/[\s\-]+/imu" =>"_",
            "/[\%;:\?\!\)\(\[\]\^\&<>\/,\$]+/imu" =>""
        ];
        return preg_replace(array_keys($reps), array_values($reps), mb_strtolower($s));
    }
};
?>
