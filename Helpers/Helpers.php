<?php

# Retorna la url del proyecto
function base_url()
{
    return BASE_URL;
}

# Formate la informacion para hacerla mas comoda de leer
function dep($data)
{
    $format = print_r('<pre>');
    $format .= print_r($data);
    $format .= print_r('</pre>');

    return $format;
}

# Elimina excesos de espacios entre palabras y evitar inyeccion sql
function strClean($strCadena)
{
    $string = preg_replace(['/\s+/', '/^\s|\s$/'], [' ', ''], $strCadena);
    $string = trim($string); # Elimina espacios en blanco al inicio y al final
    $string = stripslashes($string); #  Elimina las \ invertidas
    $string = str_ireplace("<script>", "", $string);
    $string = str_ireplace("</script>", "", $string);
    $string = str_ireplace("<script src>", "", $string);
    $string = str_ireplace("<script type=>", "", $string);
    $string = str_ireplace("SELECT * FROM", "", $string);
    $string = str_ireplace("DELETE FROM", "", $string);
    $string = str_ireplace("INSERT INTO", "", $string);
    $string = str_ireplace("SELECT COUNT(*) FROM", "", $string);
    $string = str_ireplace("DROP TABLE", "", $string);
    $string = str_ireplace("OR '1'='1", "", $string);
    $string = str_ireplace('OR "1"="1"', "", $string);
    $string = str_ireplace('OR ´1´=´1´', "", $string);
    $string = str_ireplace("is NULL; --", "", $string);
    $string = str_ireplace("in NULL; --", "", $string);
    $string = str_ireplace("LIKE '", "", $string);
    $string = str_ireplace('LIKE "', "", $string);
    $string = str_ireplace('LIKE ´', "", $string);
    $string = str_ireplace("OR 'a'='a", "", $string);
    $string = str_ireplace('OR "a"="a', "", $string);
    $string = str_ireplace("OR ´a´=´a", "", $string);
    $string = str_ireplace("OR ´a´=´a", "", $string);
    $string = str_ireplace("--", "", $string);
    $string = str_ireplace("^", "", $string);
    $string = str_ireplace("[", "", $string);
    $string = str_ireplace("]", "", $string);
    $string = str_ireplace("==", "", $string);
    return $string;
}

# Genera una contraseña de 10 caracteres
function passGenerator($length = 16)
{
    $pass = "";
    $lengthPass = $length;
    $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!#$%&'()*+,-./:;<=>?@[\]^_{|}~";
    $lengthString = strlen($string);

    for ($i = 1; $i <= $lengthPass; $i++) {
        $position = rand(0, $lengthString - 1);
        $pass .= substr($string, $position, 1);
    }

    return $pass;
}

# Genera un token
function tokenGenerator()
{
    $completeToken = '';
    for ($count = 1; $count <= 4; $count++) {
        $token = bin2hex(random_bytes(10));
        $completeToken .= $token . '-';
    }
    return trim($completeToken, '-');
}

# Formato para valores monetarios
function formatMoney($cantidad)
{
    $cantidad = number_format($cantidad, 2, SPD, SPM);
    return SMONEY.$cantidad;
}

