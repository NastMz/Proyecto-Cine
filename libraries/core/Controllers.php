<?php

class Controllers {

    public function __construct() {
        $this->loadModel();
    }

    public function loadModel() {

        # Nombre del modelo
        $model = get_class($this)."Model";

        # Ruta del archivo del modelo
        $routeClass = "models/".$model.".php";

        # Valida si el archivo del modelo existe
        if (file_exists($routeClass)) {
            # Se importa el modelo
            require_once($routeClass);
            # Se instancia el modelo
            $this->model = new $model();

        }

    }

}