<?php

class Home extends Controllers{

    public function __construct() {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function home($params) {

    }

    public function data($params) {
        echo "Data: ".$params;
    }

}