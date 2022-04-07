<?php

const BASE_URL = "http://192.168.0.105/Proyecto-Cine/";

# Datos de conexion a base de datos
const driver = ["mysql", "pgsql"];
const DB_DRIVER = driver[1];
const DB_HOST = "localhost";
const DB_NAME = "cinecolombia";
const DB_USER = "postgres";
const DB_PASSWORD = "root";
const DB_CHARSET = "charset=utf8";

# Delimitadores decimal y millar. Ej: 24,12312.00
const SPD = ",";
const SPM = ".";

# Simbolo de la moneda
const SMONEY = "$";

# Zona horaria
date_default_timezone_set('America/Bogota');