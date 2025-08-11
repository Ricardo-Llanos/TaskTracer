<?php
require_once("../.env/config.php");
require_once("../.env/conexion.php");


require_once(ROOT_PATH . "API/APIRest.php");
require_once(ROOT_PATH . "backend/Service/UserService.php");

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


    public function handleGETRequest()
    {
        //Debemos manejar los datos mediante el GET, mas no mediante el php://input
        $Id_User = $_GET['Id_User'] ?? null;
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


    public function handlePostRequest()
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


    public function handlePUTRequest()
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
            $this->UpdatePassword($Id_User, $Password);
        } else {
            $this->UpdateUser($Id_User, $Name, $PaternalSurname, $MaternalSurname);
        }
    }

    public function handlePATCHRequest(){

    }

    public function handleDELETERequest(){

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
        $statusData = isset($status["Data"]) ?? null;

        //Mostramos los resultados
        $this->sendResponse($status["StatusCode"], $status["StatusMessage"], $statusData);
    }

    private function getUserbyId(
        int $Id_User
    ) {
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
            Métodos del Método "PUT"
    =================================================*/

    private function UpdateUser(
        int $Id_User,
        string $Name,
        string $PaternalSurname,
        string $MaternalSurname
    ) {
        try {
            $query = self::$con->prepare("EXEC UpdateUser @Id_User=:Id_User, @Name=:Name, 
                                    @PaternalSurname=:PaternalSurname, @MaternalSurname=:MaternalSurname,
                                    @StatusCode=:StatusCode, @StatusMessage=:@StatusMessage");

            $query->bindParam(":Id_User", $Id_User, PDO::PARAM_INT);
            $query->bindParam(":Name", $Name, PDO::PARAM_STR);
            $query->bindParam(":PaternalSurname", $PaternalSurname, PDO::PARAM_STR);
            $query->bindParam(":MaternalSurname", $MaternalSurname, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);

            $query->execute();

            $this->sendResponse($StatusCode, $StatusMessage);
        } catch (PDOException $e) {
        } catch (Exception $e) {
        }
    }

    private function UpdatePassword(
        int $Id_User,
        string $Password
    ) {
        //Verificar que la contraseña sea fuerte


        $Password = password_hash($Password, PASSWORD_BCRYPT);

        if ($Password === false){
            $this->sendErrorResponse(500, "Error del servidor al intentar hashear la contraseña.");
            return;
        }

        $query = self::$con->prepare("EXEC UpdatePassword @Id_User=:Id_User, @Password=:Password,
                                        @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");

        $query->bindParam("Id_User", $Id_User, PDO::PARAM_INT);
        $query->bindParam("Password", $Password, PDO::PARAM_STR);
        $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
        $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);

        $query->execute();

        $this->sendResponse($StatusCode, $StatusMessage);
    }
}
