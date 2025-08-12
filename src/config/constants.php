<?php
//Variables globales API
    if (!defined("API_VERSION")) define("API_VERSION", "1.0"); //Versión de la API
    
    //Recursos de la API
    if (!defined("REGEX_REQUERIMENTS")) define("REGEX_REQUERIMENTS", __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'AllRequeriments.json'); //Definimos la ubicaciòn de las expresiones regulares

//Variables globales 
if (!defined("DEBUG_MODE")) define("DEBUG_MODE", true); //Modo DEBUG?
?>