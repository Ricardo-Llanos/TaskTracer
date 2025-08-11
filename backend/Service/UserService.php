<?php
include_once(ROOT_PATH."backend/DTO/UserDTOEntity.php");
include_once(ROOT_PATH."backend/DTO/UserDTOGet.php");
include_once(ROOT_PATH."backend/DTO/UserDTOLogin.php");
include_once(ROOT_PATH."backend/DAO/UserDAO.php");
require_once(ROOT_PATH."backend/utils/RegularExpression.php");

class UserService{
    private UserDAO $userDAO;
    private array $regexRequeriments;

    public function __construct(
        // UserDAO $user,
        PDO $Connection
    ){
        $this->userDAO = new UserDAO($Connection);

        $this->regexRequeriments = $this->regularExpressions();
    }

    private function regularExpressions(){
        if(!defined('REGEX_REQUERIMENTS')){
            throw new Exception("Los requerimientos de los datos no han sido inicializados.");
        }
        
        $data = file_get_contents(REGEX_REQUERIMENTS);
        $data = json_decode($data, associative:true);

        if(json_last_error() != JSON_ERROR_NONE){
            throw new Exception("El json brindado no pudo ser decodificado. " . json_last_error_msg());
        }

        return $data;
    }

    /*==================================
                POST
      ==================================*/

    /***
     * @param UserDTOEntity $user - 
     * @return array
     */
    public function registerUser(
        UserDTOEntity $user
    ) : array{
        //Extraemos las expresiones regulares
        $nameRe = $this->regexRequeriments['Names'] ?? null;
        $emailRe = $this->regexRequeriments['Email'] ?? null;
        $passwordRe = $this->regexRequeriments['Password'] ?? null;

        //Extraemos los datos del DTO
        $name = $user->getName();
        $paternalSurname = $user->getPaternalSurname();
        $maternalSurname = $user->getMaternalSurname();
        $email = $user->getEmail();
        $hashPassword = $user->getPassword();

        //Lógica de Negocio (Validaciones)
        //Valores vacíos
        if (trim($name) == '' || trim($paternalSurname) == '' || trim($maternalSurname) == '' ||
                    trim($email) == '' || trim($hashPassword) == ''){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La solicitud no contiene todos los valores obligatorios."
            ];
        }

        //Cumplimiento del RegEx
        else if(!preg_match($nameRe, $name, $coincidences) || !preg_match($nameRe, $paternalSurname, $coincidences)
                    || !preg_match($nameRe, $maternalSurname, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "Los nombres incluidos en la solicitud no cumplen las políticas mínimas."
            ];
        }
        else if(!preg_match($emailRe, $email, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "El email ingresado no cumple las políticas mínimas."
            ];
        }

        else if(!preg_match($passwordRe, $hashPassword, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La contraseña ingresada no cumple las políticas mínimas de seguridad."
            ];
        }

        //Validamos que el email no exista
        $existEmail = $this->userDAO->findbyEmail($email);

        if (is_array($existEmail) && sizeof($existEmail['Data']) > 0){
            $returnArray=[
                "StatusCode"=> 409,
                "StatusMessage"=> "El Email que intentas registrar ya existe."
            ];
        }

        //Ejecutamos la lógica de DB
        else{
            $hashPassword = password_hash($hashPassword, PASSWORD_BCRYPT);

            $data = array("Name"=>$name,
                            "PaternalSurname"=>$paternalSurname,
                            "MaternalSurname"=>$maternalSurname,
                            "Email"=>$email,
                            "HashPassword"=>$hashPassword);

            $returnArray = $this->userDAO->registerUser($data);
        }

        return $returnArray;
    }

    /*==================================
            GET
      ==================================*/
    
    /***
     * UserDTOGet $user - 
     * 
     */
    public function getUsers(
        UserDTOGet $user
    ) : array
    {
        $pageNumber = $user->getPageNumber();
        $pageSize = $user->getPageSize();
        $filterbyName = $user->getFilterbyName();
        $filterbyPaternalSurname = $user->getFilterbyPaternalSurname();
        $filterbyMaternalSurname = $user->getFilterbyMaternalSurname();
        $filterbyEmail = $user->getFilterbyEmail();
        $orderby = $user->getOrderby();

        if ($pageNumber=null || $pageNumber < 1){
            $pageNumber = 1;
        }
        
        if ($pageSize=null || $pageSize < 1){
            $pageSize = 20;
        }

        if (!in_array($orderby, ["Id_User", "Name", "PaternalSurname", "MaternalSurname", "Email"])){
            $orderby = "PaternalSurname";
        }

        $returnArray = $this->userDAO->getUsers($pageNumber, $pageSize, $filterbyName, $filterbyPaternalSurname,
                                        $filterbyMaternalSurname, $filterbyEmail, $orderby);

        return $returnArray;
    }

    /***
     * @param int $Id_User
     * 
     */
    public function getUserbyId(
        int $id_User
    ) : array
    {
        if ($id_User < 1){
            $returnArray=[
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 0."
            ];

        }else{
            $returnArray = $this->userDAO->getUserbyId($id_User);
        }
        
        return $returnArray;
    }
}
?>