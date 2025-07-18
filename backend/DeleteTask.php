<?php
include_once '../.env/config.php';
require_once(ROOT_PATH.".env/conexion.php");

class DeleteTask{
    private $con;

    public function __construct(){
        $this->con = Cconexion::ConnectDB();
    }

    public function deleteTask($Id_Task){
        $stmt = $this->con->prepare("EXEC DeleteTask @Id_Task=:id");
        $stmt->bindValue(":id", $Id_Task, PDO::PARAM_INT);
        $stmt->execute();

        echo "Tarea eliminada correctamente<br>";
    }
}

?>