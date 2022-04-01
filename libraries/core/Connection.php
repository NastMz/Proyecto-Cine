<?php

class Connection
{
    private $connect;

    public function __construct()
    {

        $connectionString = DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
        try {
            $this->connect = new PDO($connectionString, DB_USER, DB_PASSWORD);
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->connect = 'Error connecting to database';
            echo "Error: " . $e;
        }
    }

    public function connect()
    {
        return $this->connect;
    }

}