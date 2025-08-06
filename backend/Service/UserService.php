<?php
include_once("backend/DTO/UserDTO.php");
include_once("backend/DAO/UserDAO.php");
require_once("backend/utils/RegularExpression.php");

class UserService{
    private UserDAO $userDAO;
    private array $regexRequeriments;

    public function __construct(UserDAO $user){
        $this->userDAO = $user;

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

    public function registerUser(
        UserDTOEntity $user
    ){
        //Extraemos las expresiones regulares
        $nameRe = isset($this->regexRequeriments['Names']) ?? null;
        $emailRe = isset($this->regexRequeriments['Email']) ?? null;
        $passwordRe = isset($this->regexRequeriments['Password']) ?? null;

        //Extraemos los datos del DTO
        $name = $user->getName();
        $paternalSurname = $user->getPaternalSurname();
        $maternalSurname = $user->getMaternalSurname();
        $email = $user->getEmail();
        $hashPassword = $user->getPassword();

        //Lógica de Negocio (Validaciones)
        if (trim($name) == '' || trim($paternalSurname) == '' || trim($maternalSurname) == '' ||
                    trim($email) == '' || trim($hashPassword)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La solicitud no contiene todos los valores obligatorios."
            ];
        }

        else if(!preg_match($nameRe, $name, $coincidences) || !preg_match($nameRe, $paternalSurname, $coincidences)
                    || !preg_match($nameRe, $maternalSurname, $coincidences) || !preg_match($nameRe, $email, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "Los nombres incluidos en la solicitud no cumplen las políticas mínimas."
            ];
        }
        else if(!preg_match($emailRe, $email, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La contraseña ingresada no cumple las políticas mínimas de seguridad."
            ];
        }

        else if(!preg_match($passwordRe, $hashPassword, $coincidences)){
            $returnArray=[
                "StatusCode"=> 400,
                "StatusMessage"=> "La contraseña ingresada no cumple las políticas mínimas de seguridad."
            ];
        }

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
}
?>