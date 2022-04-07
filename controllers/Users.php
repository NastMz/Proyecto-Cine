<?php

class Users extends Controllers
{

    private array $SUCCESS = array("code" => 200, "message" => "Ejecutado correctamente.");
    private array $FAIL;
    private array $ERROR = array("code" => 500, "message" => "Ocurrio un error.");

    public function __construct()
    {
        # Se ejecuta el metodo constructor de la clase padre
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFAIL(): array
    {
        return $this->FAIL;
    }

    /**
     * @param array $KEY
     */
    public function setFAIL(array $KEY): void
    {
        $this->FAIL = array("code" => 400, "message" => "Hay campos vacios:", "fields" => $KEY);
    }

    /**
     * @return array
     */
    public function getSUCCESS(): array
    {
        return $this->SUCCESS;
    }

    /**
     * @return array
     */
    public function getERROR(): array
    {
        return $this->ERROR;
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

        $emptyKeys = $this->emptyKeys($requestData);

        if (!empty($emptyKeys)) {
            $this->setFAIL($emptyKeys);
            $message = $this->getFAIL();
            echo json_encode($message, JSON_UNESCAPED_UNICODE);
            die();
        }

        $data = [];

        foreach ($requestData as $key => $value) {
            $data[] = $value;
        }

        $request = $this->model->saveUser($data);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function find($id)
    {
        $request = $this->model->findUserById($id);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function update()
    {
        $requestData = $_POST;

        $emptyKeys = $this->emptyKeys($requestData);

        if (!empty($emptyKeys)) {
            $this->setFAIL($emptyKeys);
            $message = $this->getFAIL();
            echo json_encode($message, JSON_UNESCAPED_UNICODE);
            die();
        }

        $data = [];
        $id = "";

        foreach ($requestData as $key => $value) {
            if ($key != "user_id") {
                $data[] = $value;
            } else {
                $id = $value;
            }
        }
        $request = $this->model->updateUser($id, $data);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function findAll()
    {
        $request = $this->model->findUsers();
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function delete($id)
    {
        $request = $this->model->deleteUser($id);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
        die();
    }

    function emptyKeys($data){
        $emptyKeys = [];

        foreach ($data as $key => $value) {
            if (empty($value)) {
                $emptyKeys[] = $key;
            }
        }

        return $emptyKeys;
    }

}