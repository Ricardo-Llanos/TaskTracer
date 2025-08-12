<?php

class PasswordSecurity{
    private string $requeriments = "PasswordRequeriments.json";

    public function verifyLevelPassword($password){
        
    }

    public function verifyMinPassword($password){
        //Extraer la expresión regular desde el json
        $data = file_get_contents($this->requeriments);
        $data = json_decode($data);

        if (json_last_error() != JSON_ERROR_NONE){
            throw new Exception("El JSON no pudo ser codificado.");
        }

        //Generar la expresión regular
        $regular_expression = "";

        foreach ($data as $field){
            if (is_string($field)){
                $regular_expression.=$field;
            }
        }

        //Aplicar la expresión regular
        if (preg_match($regular_expression, $password, $coincidences)){
            echo "String validado correctamente.";
        }else{
            echo "String no válido.";
        }
    }

    public function hashPassword(string $password){
        $encryptPassword = password_hash($password, PASSWORD_BCRYPT);

        return $encryptPassword;
    }
}

$pass = new PasswordSecurity();
$pass->verifyMinPassword("1245raz");
?>