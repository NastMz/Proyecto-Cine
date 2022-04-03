<?php

class Crud extends Connection
{
    private $connection;
    private $query;
    private $values;

    function __construct()
    {
        $this->connection = new Connection;
        $this->connection = $this->connection->connect();
    }

    # Insertar un registro
    public function save(string $query, array $values)
    {
        $this->query = $query;
        $this->values = $values;

        $insert = $this->connection->prepare($this->query);
        $result = $insert->execute($this->values);

        if ($result) {
            $lastInsert = $insert->rowCount();
        } else {
            $lastInsert = 0;
        }

        return $lastInsert;
    }

    # Buscar un registro
    public function find(string $query)
    {
        $this->query = $query;

        $result = $this->connection->prepare($this->query);
        $result->execute();

        return $result->fetch(PDO::FETCH_ASSOC);
    }

    # Buscar todos los registros
    public function findAll(string $query)
    {
        $this->query = $query;

        $result = $this->connection->prepare($this->query);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    # Actualizar registros
    public function update(string $query, array $values)
    {
        $this->query = $query;
        $this->values = $values;

        $update = $this->connection->prepare($this->query);

        return $update->execute($this->values);
    }

    # Eliminar registros
    public function delete(string $query)
    {
        $this->query = $query;

        $result = $this->connection->prepare($this->query);
        $result->execute();

        return $$result;
    }
}