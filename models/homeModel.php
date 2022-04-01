<?php

class homeModel extends Database
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveUser(array $user)
    {
        $query = "INSERT INTO users(user_id, user_name, user_lastname, role_code, email, password) VALUES (?,?,?,?,?,?)";
        return $this->save($query, $user);
    }

    public function findUserById(string $id)
    {
        $query = "SELECT user_id, user_name, user_lastname, role_code, email FROM users WHERE user_id = '$id' ";
        return $this->find($query);
    }

    public function updateUser(string $id, array $user)
    {
        $query = "UPDATE users SET user_name = ?, user_lastname = ?, role_code = ?, email = ?, password = ? WHERE user_id = '$id' ";
        return $this->update($query, $user);
    }

    public function findUsers()
    {
        $query = "SELECT user_id, user_name, user_lastname, role_code, email FROM users";
        return $this->findAll($query);
    }

    public function deleteUser($id)
    {
        $query = "DELETE FROM users WHERE user_id = '$id'";
        return $this->delete($query);
    }
}
