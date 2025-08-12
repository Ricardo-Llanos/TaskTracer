<?php
// include_once("backend/GetTask.php");
// include_once("backend/InsertTask.php");
// include_once("backend/SetTask.php");
// include_once("backend/DeleteTask.php");
// require_once("frontend/index.html");
require_once("vendor/autoload.php");

//Inicializamos el archivo .env (phpdotenv)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// echo "Variable GLobal ".$_ENV['SERVER_DESA'];
use App\API\Users;

$APIUser = new Users();
$APIUser->handleRequest();

?>
