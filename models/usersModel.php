<?php

class usersModel extends Crud
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveUser(array $user)
    {
        $query = "INSERT INTO users(";
        $count = 0;
        $data = [];
        foreach ($user as $key => $value) {
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

    public function findUserById(string $id)
    {
        $query = "SELECT * FROM users WHERE user_id = '$id' ";
        return $this->find($query);
    }

    public function updateUser(array $user)
    {
        $id = $user['user_id'];
        $data = [];
        $query = "UPDATE users SET ";
        foreach ($user as $key => $value) {
            if ($key != "user_id") {
                $query .= $key . " = ?, ";
                $data[] = $value;
            }
        }

        $query = trim($query, ", ");
        $query .= " WHERE user_id = '$id'";
        return $this->update($query, $data);
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