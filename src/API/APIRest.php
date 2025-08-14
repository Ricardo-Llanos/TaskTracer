<?php

//Añadimos al psr-4
namespace Api;

//Añadimos librerías necesarias
use PDO;
use PDOException;
use Exception;

//Añadimos otros archivos del proyecto
use App\backend\DB\Cconexion;

class APIRest{

    protected static PDO $con;
    protected string $method;
    protected string $endPoint;

    /***
     * El constructor de la clase APIRest se encarga de definir la conexión a la base de datos, el
     * Content-Type necesario para la API y define una variable de clase para el "REQUEST_METHOD"
     * 
     */
    function __construct()
    {
        //Definimos el tipo de contenido
        header("Content-Type: application/json");

        //La conexión a la base de datos es vital, en caso de fallar terminaremos directamente la ejecución.
        try{
            self::$con = CConexion::ConnectDB();
        }catch(PDOException $e){
            $this->sendErrorResponse(500, "No pudo realizarse la conexión a la base de datos. Intente más tarde ".$e->getMessage());
            exit();
        }catch(Exception $e){
            $this->sendErrorResponse(500, "Ha ocurrido un error inesperado. Inténtelo más tarde. ".$e->getMessage());
            exit();
        }

        //Establecemos el método
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        /*Limpiamos el inicio y final de la URI de posibles "/". 
        Separamos la URI en un arreglo separado por el DIRECTORY_SEPARATOR. Por último extraemos el último elemento.
        */
        // $this->endPoint = end(explode(DIRECTORY_SEPARATOR, trim($_SERVER['REQUEST_URI'], '/')));
    }

    /***
     * Este método debe ser sobreescrito por las clases hija
     * 
     * @return mixed
     */
    protected function handleRequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta.");
    }

    protected function handleGETRequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta");
    }

    protected function handlePOSTRequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta");
    }

    protected function handlePUTRequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta");
    }

    protected function handlePATCHRequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta");
    }

    protected function handleDELETERequest(){
        $this->sendErrorResponse(405, "Recurso no permitido para esta ruta");
    }
    /***
     * 
     * @param int $statusCode Identifica el estado de la solicitud
     * @param string $statusMessage Identifica el mensaje de estado de la solicitud
     * @param array $data Muestra la información recolectada por la solicitud en forma de arreglo
     * 
     * @return mixed
     */
    protected function sendResponse(int $statusCode, string $statusMessage, ?string $data = "{}") : void{
        http_response_code($statusCode);
        echo json_encode(
            ["StatusCode"=> $statusCode,
            "StatusMessage"=> $statusMessage,
            "Data"=>$data]);
    }

    protected function sendErrorResponse(int $statusCode, string $errorMessage){
        $this->sendResponse(statusCode: $statusCode, 
                            statusMessage: $errorMessage);
    }

    /***
     * Este método recaba la información referente a la entrada dada para la ejecución
     * de la solicitud. En este caso devolverá un json en forma de arreglo
     * 
     * @return array - Datos extraídos del archivo interno php://input
     */
    protected function getRequest() : array
    {
        $data = file_get_contents("php://input");
        $data = json_decode($data, associative:true);

        /* Después de utilizar json_encode o json_decode es importante verificar que no haya existido ningún error
            Listado de posibles errores: https://www.php.net/manual/es/function.json-last-error.php
        */
        if (json_last_error() != JSON_ERROR_NONE){
            $this->sendErrorResponse(400, "EL json brindado no pudo ser decodificado. ".json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }
}
?>