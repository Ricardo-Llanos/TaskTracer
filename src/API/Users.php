<?php

//Añadimos al psr-4
namespace App\API;

//Añadimos librerías necesarias


//Añadimos otros archivos del proyecto
use App\API\APIRest; //Clase padre

use App\backend\Service\UserService; //Lógica de Negocio
use App\backend\DTO\UserDTOEntity; //DTO
use App\backend\DTO\UserDTOGet;
use App\backend\DTO\UserDTOLogin;
use App\backend\DTO\UserDTOPublic;
use App\backend\DTO\UserDTOUpdate;
use App\backend\DTO\UserDTOUpdatePassword;

// require_once(ROOT_PATH . "API/APIRest.php");
// require_once(ROOT_PATH . "backend/Service/UserService.php");

// //DTOs
// require_once(ROOT_PATH . "backend/DTO/UserDTOEntity.php");
// require_once(ROOT_PATH . "backend/DTO/UserDTOGet.php");
// require_once(ROOT_PATH . "backend/DTO/UserDTOLogin.php");
// require_once(ROOT_PATH . "backend/DTO/UserDTOPublic.php");
// require_once(ROOT_PATH . "backend/DTO/UserDTOUpdate.php");


class Users extends APIRest
{
    private UserService $userService;

    function __construct()
    {
        echo "API";
        parent::__construct(); //Inicializamos el constructor de la clase padre

        $this->userService = new UserService(Connection: self::$con);
    }

    public function handleRequest()
    {
        switch ($this->method) {
            case 'GET':
                // echo "Método GET";
                $this->handleGETRequest();
                break;
            case 'POST':
                // echo "Método POST";
                $this->handlePostRequest();
                break;
            case 'PUT':
                // echo "Método PUT";
                $this->handlePUTRequest();
                break;
            case 'PATCH':
                $this->handlePATCHRequest();
                break;
            case 'DELETE':
                $this->handleDELETERequest();
                break;
            default:
                echo "Método HTTP no permitido";
        }
    }


    protected function handleGETRequest()
    {
        //Debemos manejar los datos mediante el GET, mas no mediante el php://input
        $Id_User = $_GET["Id_User"] ?? null;
        $PageNumber = $_GET['PageNumber'] ?? null;
        $PageSize = $_GET['PageSize'] ?? null;
        $FilterbyName = $_GET['FilterbyName'] ?? null;
        $FilterbyPaternalSurname = $_GET['FilterbyPaternalSurname'] ?? null;
        $FilterbyMaternalSurname = $_GET['FilterbyMaternalSurname'] ?? null;
        $FilterbyEmail = $_GET['FilterbyEmail'] ?? null;
        $Orderby = $_GET['Orderby'] ?? null;

        //Verificamos a qué endpoint utilizar

        if ($Id_User) {
            $this->getUserbyId($Id_User);
        } else {
            $this->GetUsers(
                $PageNumber,
                $PageSize,
                $FilterbyName,
                $FilterbyPaternalSurname,
                $FilterbyMaternalSurname,
                $FilterbyEmail,
                $Orderby
            );
        }
    }


    protected function handlePOSTRequest()
    {
        $data = $this->getRequest();

        if (empty($data)){
            $this->sendErrorResponse(400, 'El cuerpo de la petición no puede estar vacía');
            exit();
        }

        //Verificamos los endpoint
        if ($this->endPoint === "register"){
            $this->InsertUser($data);
        }
    }


    protected function handlePUTRequest()
    {
        $Id_User = $_GET['Id_User'] ?? null; //Si 'Id_User' Existe y no es nulo
        
        if (!$Id_User){
            $this->sendErrorResponse(400, "La petición debe incluir el ID del usuario para actualizar el registro");
            return;
        }

        $data = $this->getRequest();

        if (empty($data)){
            $this->sendErrorResponse(400, "El cuerpo de la petición no puede estar vacía.");
            exit();
        }

        $Name = $data['Name'] ?? null;
        $PaternalSurname = $data['PaternalSurname'] ?? null;
        $MaternalSurname = $data['MaternalSurname'] ?? null;
        $Password = $data['Password'] ?? null;

        if ($Password) {
            // $this->UpdatePassword($Id_User, $Password);
        } else {
            $this->UpdateUser($Id_User, $Name, $PaternalSurname, $MaternalSurname);
        }
    }

    protected function handlePATCHRequest(){

    }

    protected function handleDELETERequest(){

    }

    /*================================================
            Métodos del Método "GET"
    =================================================*/

    /***
     * @param null|int $PageNumber
     */
    private function GetUsers(
        ?int $PageNumber = null,
        ?int $PageSize = null,
        ?string $FilterbyName = null,
        ?string $FilterbyPaternalSurname = null,
        ?string $FilterbyMaternalSurname = null,
        ?string $FilterbyEmail = null,
        ?string $Orderby = null
    ) {
        //Generamos el DTO
        $userDTOGet = new UserDTOGet($PageNumber, $PageSize, $FilterbyName, $FilterbyPaternalSurname,
                                $FilterbyMaternalSurname, $FilterbyEmail, $Orderby);
        
        //Ejecutamos la lógica de negocio
        $status = $this->userService->getUsers($userDTOGet);
        $statusData = isset($status["Data"]) ? $status["Data"] : null;

        //Mostramos los resultados
        $this->sendResponse($status["StatusCode"], $status["StatusMessage"], $statusData);
    }

    
    private function getUserbyId(
        int $Id_User
    ) {
        print("gteUserbyId");
        //Ejecutamos la lógica de negocio
        $status = $this->userService->getUserbyId($Id_User);
        $statusData = isset($status["Data"]) ?? null;

        //Mostramos los resultados
        $this->sendResponse($status["StatusCode"], $status["StatusMessage"], $statusData);
    }


    /*================================================
            Métodos del Método "POST"
    =================================================*/

    private function InsertUser(
        array $data
    ) {
        //Iniciamos la clase del Service
        $userDTO = new UserDTOEntity(
                    $data['Name'],
                    $data['PaternalSurname'],
                    $data['MaternalSurname'],
                    $data['Email'],
                    $data['Password']);

        //Ejecutamos la lógica referente al registro y/o inserción de usuarios
        $status = $this->userService->registerUser($userDTO);

        //Enviamos las respuestas de la lógica de servicio
        $this->sendResponse($status['StatusCode'], $status['StatusMessage']);
    }

    /*================================================
            Métodos del Verbo "PUT"
    =================================================*/

    private function UpdateUser(
        int $Id_User,
        ?string $Name,
        ?string $PaternalSurname,
        ?string $MaternalSurname
    ) {
        $userDTOUpdate = new UserDTOUpdate($Id_User, $Name, $PaternalSurname, $MaternalSurname);
        $status = $this->userService->updateUser($userDTOUpdate);

        $this->sendResponse($status['StatusCode'], $status['StatusMessage']);
    }

    /*================================================
            Métodos del Verbo "PATCH"
    =================================================*/
    private function UpdatePassword(
        int $Id_User,
        string $email,
        string $currentPassword,
        string $newPassword
    ) {
        //Definimos el DTO
        $userDTOUpdatePassword = new UserDTOUpdatePassword($Id_User, $email, $currentPassword, $newPassword);
        
        //Definimos la Lógica de Negocio
        $status = $this->userService->updatePassword($userDTOUpdatePassword);

        //Mostramos el resultado
        $this->sendResponse($status['StatusCode'], $status['StatusMessage']);
    }
}
