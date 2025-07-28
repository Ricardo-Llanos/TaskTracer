<?php

require_once(".env/conexion.php");
require_once("backend/POO/Task.php");

class APIUser extends Cconexion{

    private ?string $method;

    function __construct()
    {
        header("Content-Type: application/json");
    }

    public function StartQuery(){
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function TypeMethod(){
        
    }
}
?>