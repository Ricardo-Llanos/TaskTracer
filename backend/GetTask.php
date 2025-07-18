<?php
include_once '../.env/config.php';
include_once(ROOT_PATH.".env/conexion.php");
class GetTasks
{
    private $con;
    private $stmt;

    public function __construct()
    {
        $this->con = Cconexion::ConnectDB();
    }

    public function getTask(int $Id_Task)
    {
        $this->stmt = $this->con->prepare("EXEC GetTask @Id_Task=:id");
        $this->stmt->bindValue(":id", $Id_Task, PDO::PARAM_INT);
        $this->stmt->execute();

        $this->viewTask();
    }

    public function getAllTasks()
    {
        $this->stmt = $this->con->prepare("EXEC GetAllTasks");
        $this->stmt->execute();

        $this->viewTask();
    }

    private function viewTask()
    {
        $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        // print_r($result);
        foreach ($result as $param => &$val) {
            print_r($result[$param]);
            echo "<br>";
        }
    }

    private function aprendizaje()
    {

        include_once(".env/conexion.php");

        $con = Cconexion::ConnectDB();

        $stmt = $con->prepare("EXEC GetAllTasks");
        $stmt->execute();

        /* Existen diversos métodos para la captura de registros por medio de fetch o fetchAll

        - FETCH_ASSOC: hace que cada fila de la consulta sea devuelta como un array asociativo.
        - FETCH_NUM: Devuelve los datos, pero cada índice es numérico (No el nombre de la columna o campo).
        *- FETCH_BOTH: Devuelve un duplicado, tanto con el índice numérico como el que es textual (Se duplican)  (POR DEFECTO)
        - FETCH_OBJ: Devuelve cada fila de la consulta como un objeto
        - FETCH_COLUMN: Devuelve solo una columna en específico (La primera por defecto).
        */

        /*"fetch" captura solo 1 fila del SELECT    => Si queremos que recorra todas las filas, hacemos un while

        - Cuando usas fetch, esta función se encargará de tomar toda la data que fue seleccionada por el procedimiento almacenado
        por medio del "SELECT". Esto significa que una vez hayas tomado todos los resultados del SELECT, no podrás seguir extrayendo información.
        */

        //-- PARTE 1
        $data_1 = $stmt->fetch(PDO::FETCH_ASSOC); //-> Extrae el ID 1
        print_r($data_1);

        // while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)){ //-> Extrae el ID 2
        //     print_r($fila);
        // };

        //--PARTE 2
        //fetchAll devuelve todos los elementos seleccionados
        // $data_2 = $stmt->fetchAll(PDO::FETCH_NUM); 

        // for($i=0;$i < sizeof($data_2); $i++){
        //     print_r($data_2[$i]);
        //     echo "<br>";
        // }

        // //--PARTE 3
        // $data_3 = $stmt->fetchAll(PDO::FETCH_BOTH);
        // foreach($data_3 as $id=>$value){
        //     print_r($data_3[$id]);
        //     echo "<br>";
        // }

        // //--PARTE 4
        // $data_obj = $stmt->fetchAll(PDO::FETCH_OBJ);
        // for ($id=0; $id<sizeof($data_obj); $id++){
        //     print_r($data_obj[$id]);
        //     echo "<br>";
        // }
    }
}