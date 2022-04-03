<?php

class Dashboard extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function dashboard()
    {
        $data['page_tag'] = "Cine Colombia - Dashboard";
        $data['page_title'] = 'Dashboard';
        $data['page_name'] = "dashboard";
        $data['page_id'] = 2;
        $this->views->getView($this, "dashboard", $data);
    }

}