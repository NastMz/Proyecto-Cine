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

    public function save()
    {
        $data = array("1007590327", "Kevin Santiago", "Martinez Martinez", 1);
        $request = $this->model->saveUser($data);
        print_r($request);
    }

    public function find($id)
    {
        $request = $this->model->findUserById($id);
        print_r($request);
    }

    public function update()
    {
        $id = "1007590327";
        $data = array("Kevin Santiago", "Martinez Martinez", 1, "ksmartinez@gmail.com", "123456");
        $request = $this->model->updateUser($id, $data);
        print_r($request);
    }

    public function findAll()
    {
        $request = $this->model->findUsers();
        print_r($request);
    }

    public function delete($id)
    {
        $request = $this->model->deleteUser($id);
        print_r($request);
    }

}