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
        $data['page_name'] = "users";
        $data['page_id'] = 4;
        $this->views->getView($this, "users", $data);
    }

    public function save()
    {
        $requestData = $_POST;

        $data = [];
        foreach($requestData as $key=>$value){
             $data[] = $value;
        }

        $request = $this->model->saveUser($data);

        if ($request) {
            $message = "Guardado Correctamente";
        } else {
            $message = "Ocurrio un error";
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    public function find($id)
    {
        $request = $this->model->findUserById($id);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function update()
    {
        $id = "1007590327";
        $data = array("Kevin Santiago", "Martinez Martinez", 1, "ksmartinez@gmail.com", "123456");
        $request = $this->model->updateUser($id, $data);

        if ($request) {
            $message = "Actualizado Correctamente";
        } else {
            $message = "Ocurrio un error";
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    public function findAll()
    {
        $request = $this->model->findUsers();
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        $request = $this->model->deleteUser($id);

        if ($request) {
            $message = "Eliminado Correctamente";
        } else {
            $message = "Ocurrio un error";
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

}