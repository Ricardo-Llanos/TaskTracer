<?php
//Añadimos al psr-4
namespace backend\DAO;

//Añadimos librerías necesarias
use PDO;
use PDOException;
use Exception;

//Añadimos otros archivos del proyecto


/***
 * La Clase UserDAO es encargada de la interacción con la DB.
 * 
 * No se extienden validaciones a los datos, solo se interactúa con la DB. El dato por defecto que tendrá 
 * cada método será un "arreglo".
 * 
 * @abstract
 * @implements
 */
class UserDAO
{
    private PDO $Connection;

    public function __construct(PDO $Connection)
    {
        $this->Connection = $Connection;
    }


    /*==================================
            GET
      ==================================*/
    /***
     * @param string $email
     * @return array - El método retorna un arreglo con el status y las posibles coincidencias de usuarios con el mismo email.
     */
    public function findbyEmail(
        string $email
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_GetUserbyEmail @Email=:Email 
                                                    @StatusCode=:StatusCode @StatusMessage=:StatusMessage");
            $query->bindParam(":Email", $email, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);

            // $query->nextRowset();

            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $data = empty($data);

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage,
                "Data" => $data
            ];
        } catch (PDOException $e) {
            // error_log("Error PDO en UserDAO::findbyEmail-> "+$e->getMessage());
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error con la base de datos. Inténtelo más tarde"
            ];
            throw $e;
        }

        return $returnArray;
    }

    /***
     * Método diseñado para mantener una comunicación con la base de datos en base al verbo GET.
     * 
     * @param array{PageNumber: int, PageSize: int, FilterbyName: string, FilterbyPaternalSurname: string, FilterbyMaternalSurname: string, FilterbyEmail: string, Orderby: string} $userData  - Data necesaria para ejecutar la consulta hacia la base de datos.
     * 
     * @return array{StatusCode: mixed, StatusMessage: mixed, Data: null|mixed} - El método retorna un arreglo con el status de la consulta
     */
    public function getUsers(
        array $userData
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_GetUsers @PageNumber=:PageNumber, @PageSize=:PageSize, @FilterbyName=:FilterbyName, 
                                                    @FilterbyPaternalSurname=:FilterbyPaternalSurname, @FilterbyMaternalSurname=:FilterbyMaternalSurname,
                                                    @FilterbyEmail=:FilterbyEmail, @Orderby=:Orderby, @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":PageNumber", $userData['PageNumber'], PDO::PARAM_INT);
            $query->bindParam(":PageSize", $userData['PageSize'], PDO::PARAM_INT);
            $query->bindParam(":FilterbyName", $userData['FilterbyName'], PDO::PARAM_STR);
            $query->bindParam(":FilterbyPaternalSurname", $userData['FilterbyPaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":FilterbyMaternalSurname", $userData['FilterbyMaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":FilterbyEmail", $userData['FilterbyEmail'], PDO::PARAM_STR);
            $query->bindParam(":Orderby", $userData['Orderby'], PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();

            //Devolvemos los valores del get
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $data = json_encode($data);

            // print($data);
            if (json_last_error() != JSON_ERROR_NONE) {
                $returnArray = [
                    "StatusCode" => 500,
                    "StatusMessage" => "EL json extraído no pudo ser decodificado.",
                    "Data" => $data
                ];
                exit();
            }

            /*
            Cuando tenemos una consulta "SELECT" y parámetros "OUTPUT" debemos tomar en cuenta que SQLServer 
                trabaja con 2 rowset:
                    - SELECT: 1st Rowset
                    - OUTPUT: 2nd Rowset
            */
            $query->nextRowset(); //Sin esta instrucción los parámetros OUTPUT serán nulos

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage,
                "Data" => $data
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde. Por favor" . $e->getMessage()
            ];
        }

        return $returnArray;
    }

    public function getUserbyId(
        int $id_User
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_GetUserbyId @Id_User=:Id_User,
                                            @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Id_User", $id_User, PDO::PARAM_INT);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();

            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $data = json_encode($data);

            if (json_last_error() != JSON_ERROR_NONE) {
                $returnArray = [
                    "StatusCode" => 500,
                    "StatusMessage" => "EL json extraído no pudo ser decodificado.",
                ];
            }

            // $data = is_array($data) ?? [];

            //Mostramos el siguiente RowSet
            $query->nextRowset();

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage,
                "Data" => $data
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde." . $e->getMessage()
            ];
        }

        return $returnArray;
    }

    /***
     * @param string $email - Email del usuario a autenticar
     * @param string $hashPassword - Contraseña (hasheada) del usuario a autenticar
     * @return array{StatusCode: mixed, StatusMessage: mixed}
     */
    public function authenticateLogin(
        string $email
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_AuthenticateLogin @Email=:Email,
                                            @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Email", $email, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();


            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde. " . $e->getMessage()
            ];
        }

        return $returnArray;
    }

    /*==================================
            POST
      ==================================*/
    /***
     * @param array{Name: string, PaternalSurname: string, MaternalSurname: string, Email: string, HashPassword: string} $userData
     * @return array{StatusCode: int, StatusMessage: string}
     */
    public function registerUser(
        array $userData
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_RegisterUser @Name=:Name, @PaternalSurname=:PaternalSurname,
                                    @MaternalSurname=:MaternalSurname, @Email=:Email, @Password=:Password,
                                    @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");

            $query->bindParam(":Name", $userData['Name'], PDO::PARAM_STR);
            $query->bindParam(":PaternalSurname", $userData['PaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":MaternalSurname", $userData['MaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":Email", $userData['Email'], PDO::PARAM_STR);
            $query->bindParam(":Password", $userData['HashPassword'], PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);

            $query->execute();

            $returnArray = [
                "StatusCode" => $StatusCode,
                "StatusMessage" => $StatusMessage
            ];
        } catch (PDOException $e) {
            // error_log("Error PDO en UserDAO::registerUser -> "+$e->getMessage());
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde."
            ];
        }

        return $returnArray;
    }

    /*==================================
            PUT
      ==================================*/
    public function updateUser(
        array $userUpdate
    ) {
        try {
            $query = $this->Connection->prepare("EXEC sp_UpdateUser @Id_User=:Id_User, @Name=:Name, 
                                                    @PaternalSurname=:PaternalSurname, @MaternalSurname=:MaternalSurname,
                                                    @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");

            $query->bindParam(":Id_User", $userUpdate['Id_User'], PDO::PARAM_INT);
            $query->bindParam(":Name", $userUpdate['Name'], PDO::PARAM_STR);
            $query->bindParam(":PaternalSurname", $userUpdate['PaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":MaternalSurname", $userUpdate['MaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde." + $e->getMessage()
            ];
        }

        return $returnArray;
    }

    /*==================================
            PATCH
      ==================================*/
    public function updatePassword(
        array $arrayUpPasword
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_UpdatePassword @Id_User=:Id_User, @Email=:Email, 
                                                    @NewPassword=:NewPassword,
                                                    @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Id_User", $arrayUpPasword['Id_User'], PDO::PARAM_INT);
            $query->bindParam(":Email", $arrayUpPasword['Email'], PDO::PARAM_STR);
            $query->bindParam(":NewPassword", $arrayUpPasword['NewPassword'], PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde." . $e->getMessage()
            ];
        }
        return $returnArray;
    }

    /*==================================
            DELETE
      ==================================*/
    /***
     * @param array{Id_User: int} $userDelete
     * @return array{StatusCode: int, StatusMessage: string}
     */
    public function deleteUser(
        array $userDelete
    ) {
        try {
            $query = $this->Connection->prepare("EXEC sp_DeleteUser @Id_User=:Id_User, @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Id_User", $userDelete['Id_User'], PDO::PARAM_INT);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 400);
            $query->execute();

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde.".$e->getMessage()
            ];
        }

        return $returnArray;
    }
}
