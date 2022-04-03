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
        $this->views->getView($this, "home", $data);
    }

    public function find($id)
    {
        $request = $this->model->findMovieById($id);
        print_r($request);
    }

    public function findAll()
    {
        $request = $this->model->findMovies();
        print_r($request);
    }

}