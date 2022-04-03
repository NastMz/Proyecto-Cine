<?php

class Roles extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function roles()
    {
        $data['page_tag'] = "Cine Colombia - Roles";
        $data['page_title'] = 'Roles';
        $data['page_name'] = "roles";
        $data['page_id'] = 3;
        $this->views->getView($this, "roles", $data);
    }

}