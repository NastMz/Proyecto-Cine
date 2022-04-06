<?php

class usersModel extends Crud
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveUser(array $user)
    {
        $query = "INSERT INTO users(user_id, user_name, user_lastname, role_code, status, email, password, phone) VALUES (?,?,?,?,?,?,?,?)";
        return $this->save($query, $user);
    }

    public function findUserById(string $id)
    {
        $query = "SELECT * FROM users WHERE user_id = '$id' ";
        return $this->find($query);
    }

    public function updateUser(string $id, array $user)
    {
        $query = "UPDATE users SET user_name = ?, user_lastname = ?, role_code = ?, email = ?, password = ? WHERE user_id = '$id' ";
        return $this->update($query, $user);
    }

    public function findUsers()
    {
        $query = 'SELECT * FROM "usersView"';
        return $this->findAll($query);
    }

    public function deleteUser($id)
    {
        $query = "DELETE FROM users WHERE user_id = '$id'";
        return $this->delete($query);
    }
}