<?php
//Añadimos al psr-4
namespace App\backend\DTO;

class UserDTOUpdatePassword{
    private int $Id_User;
    private string $email;
    private string $currentPassword;
    private string $newPassword;


    /***
     * El constructor de la clase UserDTOUpdatePassword se encarga de definir los atributos de clase
     * necesarios para la actualización de la contraseña por parte del usuario.
     * 
     * @param int $Id_User - Identificador único del usuario
     * @param string $email - Email del usuario
     * @param string $currentPassword - Contraseña actual que posee el usuario
     * @param string $newPassword - Contraseña nueva que el usuario defina
     * 
     * @return void
     */
    public function __construct(
        int $Id_User,
        string $email,
        string $currentPassword,
        string $newPassword
    ){
        $this->Id_User = $Id_User;
        $this->email = $email;
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
    }

    /*==================================
                Getters
      ==================================*/
    
    public function getIdUser() : int{
        return $this->Id_User;
    }

    public function getEmail() : string{
        return $this->email;
    }

    public function getCurrentPassword() : string{
        return $this->currentPassword;
    }

    public function getNewPassword() : string{
        return $this->newPassword;
    }
}
?>