<?php
//Añadimos al psr-4
namespace App\backend\DTO;

class UserDTOUpdate{
    private int $Id_User;
    private string $name;
    private string $paternalSurname;
    private string $maternalSurname;

    public function __construct(
        int $Id_User,
        string $name,
        string $paternalSurname,
        string $maternalSurname
    ){
        $this->Id_User = $Id_User;
        $this->name = $name;
        $this->paternalSurname = $paternalSurname;
        $this->maternalSurname = $maternalSurname;
    }

    /*==================================
                Getters
      ==================================*/
    public function getIdUser() : int{
        return $this->Id_User;
    }

    public function getName() : string{
        return $this->name;
    }

    public function getPaternalSurname() : string{
        return $this->paternalSurname;
    }

    public function getMaternalSurname() : string{
        return $this->maternalSurname;
    }
}

?>