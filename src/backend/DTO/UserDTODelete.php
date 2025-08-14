<?php
//Añadimos al psr-4
namespace backend\DTO;

class UserDTODelete{
    private int $Id_User;
    private string $email;
    private string $password;

    public function __construct(
        int $Id_User,
        string $email,
        string $password
    )
    {
        $this->Id_User = $Id_User;
        $this->email = $email;
        $this->password = $password;
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

    public function getPassword() : string{
        return $this->password;
    }
}

?>