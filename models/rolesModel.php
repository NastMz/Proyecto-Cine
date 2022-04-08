<?php

class rolesModel extends Crud
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveRole(array $role)
    {
        $query = "INSERT INTO role(";
        $count = 0;
        $data = [];
        foreach ($role as $key => $value) {
            $query .= $key . ", ";
            $count += 1;
            $data[] = $value;
        }
        $query = trim($query, ", ");
        $query .= ") VALUES(";
        $query .= str_repeat("?, ", $count);
        $query = trim($query, ", ");
        $query .= ")";
        return $this->save($query, $data);
    }

    public function findRoleById(string $id)
    {
        $query = "SELECT * FROM role WHERE role_id= '$id' ";
        return $this->find($query);
    }

    public function updateRole(array $role)
    {
        $id = $role['role_id'];
        $data = [];
        $query = "UPDATE role SET ";
        foreach ($role as $key => $value) {
            if ($key != "user_id") {
                $query .= $key . " = ?, ";
                $data[] = $value;
            }
        }

        $query = trim($query, ", ");
        $query .= " WHERE role_id = '$id'";
        return $this->update($query, $data);
    }

    public function findRoles()
    {
        $query = "SELECT * FROM role";
        return $this->findAll($query);
    }

    public function deleteRole($id)
    {
        $query = "DELETE FROM role WHERE role_id = '$id'";
        return $this->delete($query);
    }
}