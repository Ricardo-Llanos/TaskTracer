<?php
include_once '../.env/config.php';
// include_once("../.env/conexion.php");

require_once(ROOT_PATH.".env/conexion.php");

class InsertTask{
    private $con;

    public function __construct(){
        $this->con = Cconexion::ConnectDB();
    }

    public function insert(string $Name){
        try{
        $stmt = $this->con->prepare("EXEC InsertTask @Name=:nam");
        $stmt->bindValue(":nam",$Name,PDO::PARAM_STR);
        $stmt->execute();


        $value = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($value);

        }catch(PDOException $e){
            echo "Ocurrió un Error durante la inserción ". $e->getMessage();
        }
        // echo "Tarea $Name insertada correctamente.<br><br>";
    }

    private function learnBindParam(string $Name){
        /*Tenemos 2 maneras de completar los parámetros dados en una llamada a un 
        procedimiento almacenado mediante PHP:

        - bindValue: El valor se fija al mismo momento de definirlo
        - bindParam: El valor se lee una vez se dé al execute
        */

        /*El problema de bindValue, es que si usamos la misma instancia preparada para insertar varios elementos
            tendremos que ir volviendo a vincular el bindValue.

            bindValue admite variables y texto plano.
        */
        $name_1 = $Name;

        $stmt = $this->con->prepare("EXEC InsertTask @Name=:nam");
        $stmt->bindValue(":nam", $name_1, PDO::PARAM_STR);
        $stmt->execute();

        //Aquí el execute seguirá usando lo definido en bindValue
        $name_1 = "Nuevo Nombre";
        $stmt->execute();
        
        //Al vincularlo nuevamente el valor sí cambiará
        $stmt->bindValue(":nam", $name_1);
        $stmt->execute();


        /*Por su parte, bindParam utiliza solo variables como parámetro. Ello genera que el valor que se utilice
            en el "execute" será el que tenga definido la varible hasta el último momento.
        */
        $name_2 = $Name;

        $stmt_2 = $this->con->prepare("EXEC InsertTask @Name:nam");
        $stmt_2->bindParam(":nam", $name_2, PDO::PARAM_STR);

        //Aquí el valor que usa execute sí cambió al de la vinculación que se hizo.
        $name_2 = $Name."Algo más";
        $stmt_2->execute();
    }
}
?>