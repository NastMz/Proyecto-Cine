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

    public function save()
    {
        $data = array("1007590327", "Kevin Santiago", "Martinez Martinez", 1);
        $request = $this->model->saveRole($data);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function find($id)
    {
        $request = $this->model->findRoleById($id);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function update()
    {
        $id = "1007590327";
        $data = array("Kevin Santiago", "Martinez Martinez", 1, "ksmartinez@gmail.com", "123456");
        $request = $this->model->updateRole($id, $data);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function findAll()
    {
        $request = $this->model->findRoles();
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        $request = $this->model->deleteRole($id);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

}