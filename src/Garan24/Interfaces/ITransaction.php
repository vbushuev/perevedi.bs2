<?php
namespace Garan24\Interfaces;
interface ITransaction{
    public function call();
    public function check();
    public function cancel();
}
?>
