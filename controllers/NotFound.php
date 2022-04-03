<?php

class NotFound extends Controllers
{

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    public function notFound()
    {
        $data['page_tag'] = "Not Found";
        $data['page_title'] = "Not Found";
        $data['page_name'] = "not_found";
        $data['page_id'] = 404;
        $this->views->getView($this, "not_found", $data);
    }

}

$notFound = new NotFound();
$notFound->notFound();