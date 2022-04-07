<?php

class rolesModel extends Crud
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveRole(array $role)
    {
        $query = "INSERT INTO roles(role_name, status) VALUES (?,?)";
        return $this->save($query, $role);
    }

    public function findRoleById(string $id)
    {
        $query = "SELECT * FROM roles WHERE role_code = '$id' ";
        return $this->find($query);
    }

    public function updateRole(string $id, array $role)
    {
        $query = "UPDATE roles SET role_name = ?, status = ? WHERE role_code = '$id' ";
        return $this->update($query, $role);
    }

    public function findRoles()
    {
        $query = "SELECT * FROM roles";
        return $this->findAll($query);
    }

    public function deleteRole($id)
    {
        $query = "DELETE FROM roles WHERE role_code = '$id'";
        return $this->delete($query);
    }
}