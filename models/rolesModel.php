<?php

class rolesModel extends Crud
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveRole(array $user)
    {
        $query = "INSERT INTO users(user_id, user_name, user_lastname, role_code, email, password) VALUES (?,?,?,?,?,?)";
        return $this->save($query, $user);
    }

    public function findRoleById(string $id)
    {
        $query = "SELECT * FROM roles WHERE role_code = '$id' ";
        return $this->find($query);
    }

    public function updateRole(string $id, array $user)
    {
        $query = "UPDATE users SET user_name = ?, user_lastname = ?, role_code = ?, email = ?, password = ? WHERE user_id = '$id' ";
        return $this->update($query, $user);
    }

    public function findRoles()
    {
        $query = "SELECT * FROM roles";
        return $this->findAll($query);
    }

    public function deleteRole($id)
    {
        $query = "DELETE FROM users WHERE user_id = '$id'";
        return $this->delete($query);
    }
}