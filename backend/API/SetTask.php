<?php
include_once '../.env/config.php';
require_once(ROOT_PATH.".env/conexion.php");

class SetTask{
    private $con;
    private $Id_Task = NULL;
    private $Description = NULL;
    private $Status = NULL;

    public function __construct()
    {
        $this->con = Cconexion::ConnectDB();
    }

    public function setAllTask(int $Id_Task, string $Description, string $Status){
        if ($Id_Task == NULL || $Description == NULL || $Status == NULL){
            throw new Exception("Class::SetTask->setAllTask: Algunos de los parámetros ingresados es nulo.");
        }
        $this->Id_Task = $Id_Task;
        $this->Description = $Description;
        $this->Status = $Status;

        $this->execute_();
    }

    public function setDescription(int $Id_Task, string $Description){
        if ($Id_Task == NULL || $Description == NULL){
            throw new Exception("Class::SetTask->setDescription: Algunos de los parámetros ingresados es nulo.");
        }
        $this->Id_Task = $Id_Task;
        $this->Description = $Description;
        $this->Status = NULL;

        $this->execute_();
    }

    public function setToDo(int $Id_Task){
        if ($Id_Task == NULL){
            throw new Exception("Class::SetTask->setToDo: Algunos de los parámetros ingresados es nulo.");
        }
        $this->Id_Task = $Id_Task;
        $this->Description = NULL;
        $this->Status = "To do";

        $this->execute_();
    }

    public function setInProgress(int $Id_Task){
        if ($Id_Task == NULL){
            throw new Exception("Class::SetTask->setInProgress: Algunos de los parámetros ingresados es nulo.");
        }
        $this->Id_Task = $Id_Task;
        $this->Description = NULL;
        $this->Status = "In Progress";

        $this->execute_();
    }

    public function setDone(int $Id_Task){
        if ($Id_Task == NULL){
            throw new Exception("Class::SetTask->setDone: Algunos de los parámetros ingresados es nulo.");
        }
        $this->Id_Task = $Id_Task;
        $this->Description = NULL;
        $this->Status = "Done";

        $this->execute_();
    }

    private function execute_(){
        $stmt = $this->con->prepare("EXEC SetTask @Id_Task=:id, @Description=:des, @Status=:sta");
        $stmt->bindValue(":id", $this->Id_Task, PDO::PARAM_INT);
        $stmt->bindValue(":des", $this->Description, PDO::PARAM_STR);
        $stmt->bindValue(":sta", $this->Status, PDO::PARAM_STR);

        $stmt->execute();

        echo "<br>Tarea editada correctamente<br>";
    }
}
?>