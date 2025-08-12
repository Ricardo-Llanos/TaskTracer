<?php

namespace env;

//En los condicionales, si la sentencia es de solo 1 línea, los "{}" son opcionales

if (!defined('ROOT_PATH')) define('ROOT_PATH',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR); //DIR => TrackTracer_DB/

/*==================================
            CONEXIÓN A DB
==================================*/
if (!defined('SERVER_DESA')) define('SERVER_DESA', 'localhost'); //Se establece el Servidor de Desarrollo
if (!defined('PORT_DESA')) define ('PORT_DESA', '1433'); //Se establece el puerto del Servidor de Desarrollo
if (!defined('DB_CONNECT')) define('DB_CONNECT', 'TrackTracer'); //Base de datos a la cual nos conectaremos

//Credenciales
if (!defined('SESSION_USER')) define('SESSION_USER', 'LoginPHP'); //Usuario DB
if (!defined('SESSION_PASSWORD')) define('SESSION_PASSWORD', 'PHPTest12345'); //contraseña DB

/*==================================
            REQUERIMENTS
==================================*/
if (!defined('REGEX_REQUERIMENTS')) define('REGEX_REQUERIMENTS', 'backend'.DIRECTORY_SEPARATOR.'utils'.DIRECTORY_SEPARATOR.'AllRequeriments.json');
?>