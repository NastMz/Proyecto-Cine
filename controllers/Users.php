<?php

class Users extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function users()
    {
        $data['page_tag'] = "Cine Colombia - Usuarios";
        $data['page_title'] = 'Usuarios';
        $data['page_name'] = "usuarios";
        $data['page_id'] = 4;
        $this->views->getView($this, "users", $data);
    }

}