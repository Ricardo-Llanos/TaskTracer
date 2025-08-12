<?php
//Añadimos al psr-4
namespace App\backend\Service;

//Añadimos librerías necesarias
use PDO;
use PDOException;
use Exception;

//Añadimos otros archivos del proyecto
use App\backend\DAO\UserDAO;

use App\backend\DTO\UserDTOEntity;
use App\backend\DTO\UserDTOGet;
use App\backend\DTO\UserDTOLogin;
use App\backend\DTO\UserDTOPublic;
use App\backend\DTO\UserDTOUpdate;
use App\backend\DTO\UserDTOUpdatePassword;

use App\backend\utils\RegularExpression;

class UserService implements RegularExpression{
    private UserDAO $userDAO;
    private array $regexRequeriments;

    /***
     * El constructor de la clase UserService define el DAO de la tabla User y LoginUser. A su vez, también se definen los requerimientos de entrada de datos en forma de expresiones regulares.
     * 
     * @param PDO $Connection - Instancia de la librería PDO necesaria para la conexión a la base de datos.
     * @return void
     */
    public function __construct(
        // UserDAO $user,
        PDO $Connection
    ){
        $this->userDAO = new UserDAO($Connection);
        
        $this->regexRequeriments = $this->extractRegex(REGEX_REQUERIMENTS);
    }

    /***
     * La función extractRegex define en formato de arreglo todas las expresiones regulares
     * necesarias para el funcionamiento idóneo de la API
     * 
     * @return array
     */
    public function extractRegex(string $pathResource) : array{
        try{
            $data = file_get_contents($pathResource);
        }catch(Exception $e){
            throw new Exception("La dirección proporcionada de los datos no existe. "+$e->getMessage());
        }

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
     * @param UserDTOEntity $user - Instancia de la clase UserDTOEntity que almacena los datos de entrada necesarios para el correcto registro del usuario.
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
            // $orderby = "PaternalSurname";
            $orderby = "Id_User";
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
        if ($id_User < 1 || trim($id_User) == ""){
            $returnArray=[
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 0."
            ];

        }else{
            $returnArray = $this->userDAO->getUserbyId($id_User);
        }
        
        return $returnArray;
    }

    /*==================================
            PUT
      ==================================*/
    
    public function updateUser(
        UserDTOUpdate $userUpdate
    ) : array{
        $nameRe = $this->regexRequeriments['Names'];

        $Id_User = $userUpdate->getIdUser();
        $name = $userUpdate->getName();
        $paternalSurname = $userUpdate->getPaternalSurname();
        $maternalSurname = $userUpdate->getMaternalSurname();

        if ($Id_User < 0){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "El Id proporcionado no es correcto. Id menor a 0."
            ];
        }
        
        if (trim($name)== "" & trim($paternalSurname)=="" & trim($maternalSurname)==""){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La solicitud de Update no contiene ningún valor a modificar"
            ];
        }

        if ((!preg_match($nameRe, $name) & trim($name) != "") || (!preg_match($nameRe, $paternalSurname) & trim($paternalSurname) != "") ||
                (!preg_match($nameRe, $maternalSurname) & trim($maternalSurname) != "")){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "Los nombres incluidos en la solicitud no cumplen las políticas mínimas."
            ];
        }

        else{
            $arrayUpdate = ["Id_User"=> $Id_User, 
            "Name"=> $name, 
            "PaternalSurname"=>$paternalSurname, 
            "MaternalSurname"=>$maternalSurname];

            $returnArray = $this->userDAO->updateUser($arrayUpdate);
        }

        return $returnArray;
    }

    /*==================================
            PATCH
      ==================================*/
    
    /***
     * @param UserDTOUpdatePassword $userUpPassword - Instancia de la clase UserDTOUpdatePassword en la cual se definen los datos de entrada necesarios para actualizar la contraseña del usuario.
     * @return array{StatusCode: mixed, StatusMessage: mixed} - El método retorna un arreglo con los datos del Status.
     */
    public function updatePassword(
        UserDTOUpdatePassword $userUpPassword
    ) : array{
        $emailRe = $this->regexRequeriments['Email'];
        $passwordRe = $this->regexRequeriments['Password'];

        $Id_User = $userUpPassword->getIdUser();
        $email = $userUpPassword->getEmail();
        $currentPassword = $userUpPassword->getCurrentPassword();
        $newPassword = $userUpPassword->getNewPassword();

        if ($Id_User < 0){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=>"El Id proporcionado no es correcto. Id menor a 0."
            ];
        }

        if (trim($email) == "" || trim($currentPassword) == "" || trim($newPassword) == ""){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=>"La solicitud no contiene todos los valores obligatorios."
            ];
        }

        if (!preg_match($emailRe, $email) || !preg_match($passwordRe, $currentPassword) || !preg_match($passwordRe, $newPassword)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=>"El email ingresado no cumple las políticas mínimas."
            ];
        }

        if (!preg_match($passwordRe, $currentPassword) || !preg_match($passwordRe, $newPassword)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "Las contraseñas ingresadas no cumplen las políticas mínimas de seguridad."
            ];
        }

        else{
            $currentPassword = password_hash($currentPassword, PASSWORD_BCRYPT);
            $newPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $arrayData = [
                "Id_User" => $Id_User,
                "Email" => $email,
                "CurrentPassword" => $currentPassword,
                "NewPassword" => $newPassword
            ];

            $returnArray = $this->userDAO->updatePassword(arrayUpPasword: $arrayData);
        }

        return $returnArray;
    }
}
?>