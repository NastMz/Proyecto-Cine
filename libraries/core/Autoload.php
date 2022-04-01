<?php

# Carga automaticamente todas las clases de un controlador
spl_autoload_register(function ($class) {
    $filename = LIBRARIES . 'Core/' . $class . '.php';
    if (file_exists($filename)) {
        require_once($filename);
    }
});