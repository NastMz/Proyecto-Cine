<?php

class Home extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function home($params)
    {
        $this->views->getView($this, "home");
    }

}