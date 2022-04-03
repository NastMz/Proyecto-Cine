<?php

$controller = ucwords($controller);
$controllerFile = "Controllers/" . $controller . ".php";
# Valida si el archivo controlador existe
if (file_exists($controllerFile)) {

    # Se importa el archivo
    require_once($controllerFile);
    # Se instancia la clase del contorlador
    $controller = new $controller();

    # Se valida si el metodo de la url existe en el controlodar
    if (method_exists($controller, $method)) {
        # Se llama al metodo con los parametros de la url
        $controller->{$method}($params);
    } else {
        require_once('Controllers/NotFound.php');
    }

} else {
    require_once('Controllers/NotFound.php');
}