<?php

class Roles extends Controllers
{
    private array $SUCCESS = array("code" => 200, "message" => "Ejecutado correctamente.");
    private array $FAIL;
    private array $ERROR = array("code" => 500, "message" => "Ocurrio un error.");

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
        $requestData = $_POST;

        $emptyKeys = $this->emptyKeys($requestData);

        if (!empty($emptyKeys)) {
            $this->setFAIL($emptyKeys);
            $message = $this->getFAIL();
            echo json_encode($message, JSON_UNESCAPED_UNICODE);
            die();
        }

        $request = $this->model->saveRole($requestData);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    function emptyKeys($data)
    {
        $emptyKeys = [];

        foreach ($data as $key => $value) {
            if (empty($value)) {
                $emptyKeys[] = $key;
            }
        }

        return $emptyKeys;
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

    public function find($id)
    {
        $request = $this->model->findRoleById($id);
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function update()
    {
        $requestData = $_POST;

        $emptyKeys = $this->emptyKeys($requestData);

        if (!empty($emptyKeys)) {
            $this->setFAIL($emptyKeys);
            $message = $this->getFAIL();
            echo json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $request = $this->model->updateRole($requestData);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    public function findAll()
    {
        $request = $this->model->findRoles();
        echo json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        $request = $this->model->deleteRole($id);

        if ($request) {
            $message = $this->getSUCCESS();
        } else {
            $message = $this->getERROR();
        }
        echo json_encode($message, JSON_UNESCAPED_UNICODE);
    }

}