<?php

class Home extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function home()
    {
        $data['page_tag'] = "Cine Colombia";
        $data['page_title'] = "Home";
        $data['page_name'] = "home";
        $data['page_id'] = 1;
        $data['page_content'] = "lorem ipsum dolor sit amet, consectetur adip";
        $this->views->getView($this, "home", $data);
    }

}