<?php
//Añadimos al psr-4
namespace backend\Service;

//Añadimos librerías necesarias
use PDO;
use PDOException;
use Exception;

//Añadimos otros archivos del proyecto
use backend\DAO\UserDAO;

use backend\DTO\UserDTOEntity;
use backend\DTO\UserDTOGet;
use backend\DTO\UserDTOLogin;
use backend\DTO\UserDTOPublic;
use backend\DTO\UserDTOUpdate;
use backend\DTO\UserDTOUpdatePassword;
use backend\DTO\UserDTODelete;

use App\backend\utils\RegularExpression;

class UserService implements RegularExpression
{
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
    ) {
        $this->userDAO = new UserDAO($Connection);

        $this->regexRequeriments = $this->extractRegex(REGEX_REQUERIMENTS);
    }

    /***
     * La función extractRegex define en formato de arreglo todas las expresiones regulares
     * necesarias para el funcionamiento idóneo de la API
     * 
     * @return array{Names: mixed, Email: mixed, Password: mixed}
     */
    public function extractRegex(string $pathResource): array
    {
        try {
            $data = file_get_contents($pathResource);
        } catch (Exception $e) {
            throw new Exception("La dirección proporcionada de los datos no existe. " + $e->getMessage());
        }

        $data = json_decode($data, associative: true);

        if (json_last_error() != JSON_ERROR_NONE) {
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
    ): array {
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
        if (
            trim($name) == '' || trim($paternalSurname) == '' || trim($maternalSurname) == '' ||
            trim($email) == '' || trim($hashPassword) == ''
        ) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "La solicitud no contiene todos los valores obligatorios."
            ];
        }

        //Cumplimiento del RegEx
        else if (
            !preg_match($nameRe, $name, $coincidences) || !preg_match($nameRe, $paternalSurname, $coincidences)
            || !preg_match($nameRe, $maternalSurname, $coincidences)
        ) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "Los nombres incluidos en la solicitud no cumplen las políticas mínimas."
            ];
        } else if (!preg_match($emailRe, $email, $coincidences)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El email ingresado no cumple las políticas mínimas."
            ];
        } else if (!preg_match($passwordRe, $hashPassword, $coincidences)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "La contraseña ingresada no cumple las políticas mínimas de seguridad."
            ];
        }

        //Ejecutamos la lógica de DB
        else {
            //Validamos que el email no exista
            $existEmail = $this->userDAO->findbyEmail($email);

            if (is_array($existEmail) && isset($existEmail['Data']) && $existEmail['Data'] == 1) {
                $returnArray = [
                    "StatusCode" => 409,
                    "StatusMessage" => "El Email que intentas registrar ya existe."
                ];
            } else {
                $hashPassword = password_hash($hashPassword, PASSWORD_BCRYPT);

                $data = array(
                    "Name" => $name,
                    "PaternalSurname" => $paternalSurname,
                    "MaternalSurname" => $maternalSurname,
                    "Email" => $email,
                    "HashPassword" => $hashPassword
                );

                $returnArray = $this->userDAO->registerUser($data);
            }
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
    ): array {
        $pageNumber = $user->getPageNumber();
        $pageSize = $user->getPageSize();
        $filterbyName = $user->getFilterbyName();
        $filterbyPaternalSurname = $user->getFilterbyPaternalSurname();
        $filterbyMaternalSurname = $user->getFilterbyMaternalSurname();
        $filterbyEmail = $user->getFilterbyEmail();
        $orderby = $user->getOrderby();

        if ($pageNumber = null || $pageNumber < 1) {
            $pageNumber = 1;
        }

        if ($pageSize = null || $pageSize < 1) {
            $pageSize = 20;
        }

        if (!in_array($orderby, ["Id_User", "Name", "PaternalSurname", "MaternalSurname", "Email"])) {
            $orderby = "PaternalSurname";
            // $orderby = "Id_User";
        }
        // $data = [
        //     "PageNumber" = 
        // ]
        $returnArray = $this->userDAO->getUsers(
            [
                'PageNumber' => $pageNumber,
                'PageSize' => $pageSize,
                'FilterbyName' => $filterbyName,
                'FilterbyPaternalSurname' => $filterbyPaternalSurname,
                'FilterbyMaternalSurname' => $filterbyMaternalSurname,
                'FilterbyEmail' => $filterbyEmail,
                'Orderby' => $orderby
            ],
        );

        return $returnArray;
    }

    /***
     * El método getUserbyId se encarga de devolver los registros de la DB de la tabla UsersT que coincidan con el Id especificado
     * 
     * @param int $Id_User - Identificador único del usuario
     * @return array{StatusCode: null|int, StatusMessage: null|string, Data: null|string}
     */
    public function getUserbyId(
        int $Id_User
    ): array {
        if ($Id_User < 1) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 0."
            ];
        } else {
            $returnArray = $this->userDAO->getUserbyId($Id_User);
        }

        return $returnArray;
    }

    /*==================================
            PUT
      ==================================*/

    public function updateUser(
        UserDTOUpdate $userUpdate
    ): array {
        $nameRe = $this->regexRequeriments['Names'];

        $Id_User = $userUpdate->getIdUser();
        $name = $userUpdate->getName();
        $paternalSurname = $userUpdate->getPaternalSurname();
        $maternalSurname = $userUpdate->getMaternalSurname();

        if ($Id_User < 0) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 0."
            ];
        }

        if (trim($name) == "" & trim($paternalSurname) == "" & trim($maternalSurname) == "") {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "La solicitud de Update no contiene ningún valor a modificar"
            ];
        }

        if ((!preg_match($nameRe, $name) & trim($name) != "") || (!preg_match($nameRe, $paternalSurname) & trim($paternalSurname) != "") ||
            (!preg_match($nameRe, $maternalSurname) & trim($maternalSurname) != "")
        ) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "Los nombres incluidos en la solicitud no cumplen las políticas mínimas."
            ];
        } else {
            $arrayUpdate = [
                "Id_User" => $Id_User,
                "Name" => $name,
                "PaternalSurname" => $paternalSurname,
                "MaternalSurname" => $maternalSurname
            ];

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
    ): array {
        $emailRe = $this->regexRequeriments['Email'];
        $passwordRe = $this->regexRequeriments['Password'];

        $Id_User = $userUpPassword->getIdUser();
        $email = $userUpPassword->getEmail();
        $currentPassword = $userUpPassword->getCurrentPassword();
        $newPassword = $userUpPassword->getNewPassword();

        if ($Id_User < 0) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 0."
            ];
        }

        if (trim($email) == "" || trim($currentPassword) == "" || trim($newPassword) == "") {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "La solicitud no contiene todos los valores obligatorios."
            ];
        }

        if (!preg_match($emailRe, $email)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El email ingresado no cumple las políticas mínimas."
            ];
        }

        // if (!preg_match($passwordRe, $currentPassword) || !preg_match($passwordRe, $newPassword)){
        if (!preg_match($passwordRe, $newPassword) || !preg_match($passwordRe, $currentPassword)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "Las contraseñas ingresadas no cumplen las políticas mínimas de seguridad."
            ];
        } else {
            $data = $this->userDAO->authenticateLogin($email);
            if (isset($data['StatusMessage']) && $data['StatusCode'] == 202) {
                $actualHashPassword = $data['StatusMessage'];

                if (password_verify($currentPassword, $actualHashPassword)) {
                    $newPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    $arrayData = [
                        "Id_User" => $Id_User,
                        "Email" => $email,
                        "NewPassword" => $newPassword
                    ];

                    $returnArray = $this->userDAO->updatePassword(arrayUpPasword: $arrayData);
                } else {
                    $returnArray = [
                        "StatusCode" => 400,
                        "StatusMessage" => "Email y/o Password incorrectos."
                    ];
                }
            }
        }

        return $returnArray;
    }

    /***
     * @param UserDTOLogin $userData - Instancia de la clase UserDTOLogin necesaria para ejecutar la lógica de negocio y consultas a la DB
     * @return array{StatusCode: mixed, StatusMessage: mixed}
     * 
     */
    public function authenticateLogin(
        UserDTOLogin $userData
    ): array {
        $emailRe = $this->regexRequeriments['Email'];
        $passwordRe = $this->regexRequeriments['Password'];

        $email = $userData->getEmail();
        $password = $userData->getPassword();

        if (trim($email) == '' || trim($password) == '') {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "La solicitud no contiene todos los valores obligatorios."
            ];
        } elseif (!preg_match($emailRe, $email) || !preg_match($passwordRe, $password)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El email/contraseña ingresado no cumple las políticas mínimas."
            ];
        } else {
            $returnArray = $this->userDAO->authenticateLogin(email: $email);

            if (isset($returnArray['StatusMessage']) && $returnArray['StatusCode'] == 202) {
                $actualHashPassword = $returnArray['StatusMessage'];

                $decision = password_verify($password, $actualHashPassword);

                if ($decision) {
                    $returnArray = [
                        "StatusCode" => 200,
                        "StatusMessage" => "Autenticación correcta"
                    ];
                } else {
                    $returnArray = [
                        "StatusCode" => 400,
                        "StatusMessage" => "Email / Password incorrectos."
                    ];
                }
            }
        }

        return $returnArray;
    }

    /***
     * @param UserDTODelete $userData
     * @return array{StatusCode: int, StatusMessage: string}
     */
    public function deleteUser(
        UserDTODelete $userData
    ): array {
        $emailRe = $this->regexRequeriments['Email'];
        $passwordRe = $this->regexRequeriments['Password'];

        $Id_User = $userData->getIdUser();
        $email = $userData->getEmail();
        $password = $userData->getPassword();

        if ($Id_User < 1) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El Id proporcionado no es correcto. Id menor a 1."
            ];
        } elseif (!preg_match($emailRe, $email)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "El email ingresado no cumple las políticas mínimas."
            ];
        } elseif (!preg_match($passwordRe, $password)) {
            $returnArray = [
                "StatusCode" => 400,
                "StatusMessage" => "Las contraseñas ingresadas no cumplen las políticas mínimas de seguridad."
            ];
        } else{
            $data = $this->userDAO->authenticateLogin($email);

            if (isset($data['StatusMessage']) || $data['StatusCode'] == 202){
                if (password_verify($password, $data['StatusMessage'])){
                    $returnArray = $this->userDAO->deleteUser(["Id_User"=>$Id_User]);
                }
                else{
                    $returnArray = [
                        "StatusCode" => 400,
                        "StatusMessage" => "Email / Password incorrectos."
                    ];
                }
            }
        }

        return $returnArray;
    }
}
