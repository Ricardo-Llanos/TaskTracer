<?php

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

    /***
     * @param string $email
     */
    public function findbyEmail(string $email): array
    {
        try {
            $query = $this->Connection->prepare("EXEC sp_GetUserbyEmail @Email=:Email 
                                                    @StatusCode=:StatusCode @StatusMessage=:StatusMessage");
            $query->bindParam(":Email", $email, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4000);

            $query->nextRowset();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            $data = empty($data);

            $returnArray = [
                "StatusCode" => $StatusCode,
                "StatusMessage" => $StatusMessage,
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

    public function registerUser(array $userData): array
    {
        try {
            $query = $this->Connection->prepare("EXEC sp_RegisterUser @Name=:Name, @PaternalSurname=:PaternalSurname,
                                    @MaternalSurname=:MaternalSurname, @Email=:Email, @Password=:Password,
                                    @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");

            $query->bindParam(":Name", $userData['Name'], PDO::PARAM_STR);
            $query->bindParam(":PaternalSurname", $userData['PaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":MaternalSurname", $userData['MaternalSurname'], PDO::PARAM_STR);
            $query->bindParam(":Email", $userData['Email'], PDO::PARAM_STR);
            $query->bindParam(":Password", $userData['HashPassword'], PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $StatusCode, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $StatusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);

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

    public function getUsers(
        ?int $pageNumber = null,
        ?int $pageSize = null,
        ?string $filterbyName = null,
        ?string $filterbyPaternalSurname = null,
        ?string $filterbyMaternalSurname = null,
        ?string $orderby = null,
        ?int $statusCode = null,
        ?string $statusMessage = null,
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_GetUsers @PageNumber=:PageNumber, @PageSize=:PageSize, @FilterbyName=:FilterbyName, 
                                                    @FilterbyPaternalSurname=:FilterbyPaternalSurname, @FilterbyMaternalSurname=:FilterbyMaternalSurname,
                                                    @FilterbyEmail=:FilterbyEmail, @Orderby=:Orderby, @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":PageNumber", $pageNumber, PDO::PARAM_INT);
            $query->bindParam(":PageSize", $pageSize, PDO::PARAM_INT);
            $query->bindParam(":FilterbyName", $filterbyName, PDO::PARAM_STR);
            $query->bindParam(":FilterbyPaternalSurname", $filterbyPaternalSurname, PDO::PARAM_STR);
            $query->bindParam(":FilterbyMaternalSurname", $filterbyMaternalSurname, PDO::PARAM_STR);
            $query->bindParam(":FilterbyEmail", $filterbyEmail, PDO::PARAM_STR);
            $query->bindParam(":Orderby", $orderby, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $query->execute();

            //Al tener valores de tipo output debemos avanzar hacia el siguiente RowSet
            $query->nextRowset();

            //Devolvemos los valores del get
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $data = json_encode($data);

            if (json_last_error() != JSON_ERROR_NONE) {
                $returnArray = [
                    "StatusCode" => 500,
                    "StatusMessage" => "EL json extraído no pudo ser decodificado.",
                    "Data" => $data
                ];
            }

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage,
                "Data" => $data
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde."
            ];
        }

        return $returnArray;
    }

    public function getUserbyId(int $id_User) : array
    {
        try{
            $query = $this->Connection->prepare("EXEC sp_GetUserbyId @Id_User=:Id_User,
                                            @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Id_User", $id_User, PDO::PARAM_INT);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->execute();

            $query->nextRowset();

            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $data = json_encode($data);

            if (json_last_error() != JSON_ERROR_NONE){
                $returnArray = [
                    "StatusCode"=> 500,
                    "StatusMessage"=> "EL json extraído no pudo ser decodificado.", 
                ];
            }

            $data = is_array($data) ?? [];
            
            $returnArray = [
                    "StatusCode"=> $statusCode,
                    "StatusMessage"=> $statusMessage, 
                    "Data"=> $data
            ];
            
        }catch(PDOException $e){
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde."
            ];
        }

        return $returnArray;
    }

    public function authenticateLogin(
        string $email,
        string $hashPassword
    ): array {
        try {
            $query = $this->Connection->prepare("EXEC sp_AuthenticateLogin @Email=:Email, @HashPassword=:HashPassword, 
                                            @StatusCode=:StatusCode, @StatusMessage=:StatusMessage");
            $query->bindParam(":Email", $email, PDO::PARAM_STR);
            $query->bindParam(":Password", $hashPassword, PDO::PARAM_STR);
            $query->bindParam(":StatusCode", $statusCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 4);
            $query->bindParam(":StatusMessage", $statusMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4);

            $returnArray = [
                "StatusCode" => $statusCode,
                "StatusMessage" => $statusMessage
            ];
        } catch (PDOException $e) {
            $returnArray = [
                "StatusCode" => 500,
                "StatusMessage" => "Error en la base de datos. Inténtelo más tarde"
            ];
        }

        return $returnArray;
    }
}
