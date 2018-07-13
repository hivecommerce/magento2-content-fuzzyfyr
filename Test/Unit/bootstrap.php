<?php

require_once './vendor/autoload.php';

if (!class_exists('\\Zend_Db_Select')) {
    class Zend_Db_Select {}
}