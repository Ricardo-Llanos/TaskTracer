<?php
require_once(ROOT_PATH . "backend/API/api_rest.php");
require_once(ROOT_PATH . "backend/Service/UserService.php");

class APIUser extends APIRest
{

    function __construct()
    {
        echo "API";
        parent::__construct(); //Inicializamos el constructor de la clase padre
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

        if ($Id_User) {
            $this->getUserbyId($Id_User);
        } else {
            $this->GetUsers();
            // $this->GetUsers(
            //     $PageNumber,
            //     $PageSize,
            //     $FilterbyName,
            //     $FilterbyPaternalSurname,
            //     $FilterbyMaternalSurname,
            //     $FilterbyEmail,
            //     $Orderby
            // );
        }
    }


    public function handlePostRequest()
    {
        $data = $this->getRequest();
        if (empty($data)){
            $this->sendErrorResponse(400, 'El cuerpo de la petición no puede estar vacía');
            exit();
        }

        $conti = $this->InsertSomeUser($data);

        if (!$conti){
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
     * @param ?int $PageNumber
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
        // if ($PageNumber<1){
        //     $PageNumber = 1;
        // }

        // if ($PageSize<1){
        //     $PageSize = 1;
        // }

        // if ($FilterbyName || $FilterbyPaternalSurname || $FilterbyMaternalSurname || $FilterbyEmail){

        // }

        $StatusCode = 0;
        $StatusMessage = "";

        //Ejecutamos la consulta (Aquí no hace falta agregar el término OUTPUT)
        $query = self::$con->prepare("EXEC GetUsers @PageNumber=:PageNumber, @PageSize=:PageSize,
                                        @FilterbyName=:FilterbyName, @FilterbyPaternalSurname=:FilterbyPaternalSurname,
                                        @FilterbyMaternalSurname=:FilterbyMaternalSurname, @FilterbyEmail=:FilterbyEmail,                                        
                                        @Orderby=:Orderby, @StatusCode=:StatusCode,
                                        @StatusMessage=:StatusMessage");
        $query->bindParam(":PageNumber", $PageNumber, PDO::PARAM_INT);
        $query->bindParam(":PageSize", $PageSize, PDO::PARAM_INT);
        $query->bindParam(":FilterbyName", $FilterbyName, PDO::PARAM_STR);
        $query->bindParam(":FilterbyPaternalSurname", $FilterbyPaternalSurname, PDO::PARAM_STR);
        $query->bindParam(":FilterbyMaternalSurname", $FilterbyMaternalSurname, PDO::PARAM_STR);
        $query->bindParam(":FilterbyEmail", $FilterbyEmail, PDO::PARAM_STR);
        $query->bindParam(":Orderby", $Orderby, PDO::PARAM_STR);

        /*Los parámetros de OUTPUT necesitan ese tipo de parámetro junto a su valor en memoria
            4 para un int y 4000 para un VARCHAR(MAX) o NVARCHAR(MAX) suele ser suficiente (Es el por defecto o estándar)
        */
        $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
        $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
        $query->execute();

        /*El procedimiento almacenado tiene parámetros OUTPUT, por lo que necesitamos avanzar al sguiente ROWSET
            Por defecto 
        */
        $query->nextRowset();

        //Obtenemos los datos
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        //Convertimos los datos a un json
        $data = json_encode($data);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->sendErrorResponse(400, "EL json brindado no pudo ser decodificado. " . json_last_error_msg());
            exit();
        }

        $data = is_array($data) ? $data : [];
        $this->sendResponse($StatusCode, $StatusMessage, $data);
    }

    private function getUserbyId(
        int $Id_User
    ) {
        // Verificamos integridad
        if ($Id_User < 1) {
            $this->sendErrorResponse(400, "El Id del usuario proporcionado es incorrecto.");
            exit();
        }

        //Ejecutamos el query
        $query = self::$con->prepare("EXEC GetUserbyId @Id_User=:Id_User, @StatusCode=:StatusCode, 
                                        @StatusMessage=:StatusMessage");
        $query->bindParam(":Id_User", $Id_User, PDO::PARAM_INT);
        $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
        $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
        $query->execute();

        //Obtenemos los datos
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode($data);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->sendErrorResponse(400, "El json brindado no pudo ser decodificado.");
            exit();
        }

        $data = is_array($data) ? $data : [];
        $this->sendResponse($StatusCode, $StatusMessage, $data);
    }


    /*================================================
            Métodos del Método "POST"
    =================================================*/

    private function InsertUser(
        array $data
    ) {
        //Iniciamos la clase del Service
        $service = new UserService(self::$con);
        $userDTO = new UserDTOEntity(
                    $data['Name'],
                    $data['PaternalSurname'],
                    $data['MaternalSurname'],
                    $data['Email'],
                    $data['Password']);

        //Ejecutamos la lógica referente al registro y/o inserción de usuarios
        $status = $service->registerUser($userDTO);

        //Enviamos las respuestas de la lógica de servicio
        $this->sendResponse($status['StatusCode'], $status['StatusMessage']);
    }

    private function InsertSomeUser(
        array $multiData
    ) : bool {

        $insert = false;
        foreach ($multiData as $data){
            if (is_array($data)){
                foreach($data as $semiData){
                    if (is_array($semiData)){
                        $this->sendErrorResponse(400, "El JSON brindado es incorrecto. No se aceptan más de 2 dimensiones.");
                    }
                }

                $this->InsertUser($data);
                if (!$insert){
                    $insert = true;
                }
            }
        }

        return $insert;
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
