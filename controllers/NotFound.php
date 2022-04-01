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
        $this->views->getView($this, "not_found");
    }

}

$notFound = new NotFound();
$notFound->notFound();