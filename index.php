<?php
require_once("config/Config.php");

# Se obtiene la url
$route = !empty($_GET['route']) ? $_GET['route'] : 'home/home';

# Array que almacena las rutas separadas de la url
$arrayRoute = explode('/', $route);

# Variable que almacena la primera ruta que va a ser el controlador
$controller = $arrayRoute[0];

# Variable que almacena la segunda ruta que va a ser el metodo,
# por defecto es el mismo para el caso que sea la pagina principal
$method = $arrayRoute[0];

# Variable que almacena los posibles parametros de la url
$params = "";

# Se valida si existe algun valor en la posicion 1 para obtener el metodo
if (!empty($arrayRoute[1]) && $arrayRoute[1] != "") {
    $method = $arrayRoute[1];
}

# Se valida si existe algun valor en la posicion 2 para obtener los parametros
if (!empty($arrayRoute[2]) && $arrayRoute[2] != "") {

    # Se concatenan todos los posibles parametros de la url.
    # Ej: Si la url es /usuario/pepe/2
    # entonces params = usuario,pepe,2,
    for ($i = 2; $i < count($arrayRoute); $i++) {
        $params .= $arrayRoute[$i] . ',';
    }
    # Se elimina la coma final de params
    $params = trim($params, ',');
}

# Carga automaticamente los controladores
spl_autoload_register(function ($class) {
    $filename = LIBRARIES.'core/'.$class.'.php';
    if (file_exists($filename)) {
        require_once($filename);
    }
});

# Load
$controllerFile = "controllers/".$controller.".php";
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
    }else {
        echo "Method not found.";
    }

} else {
    echo "Controller file not found.";
}