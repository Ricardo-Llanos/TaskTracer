<?php

/***
 * La clase UserDTOEntity sirve para 
 * 
 * @abstract -
 * @implements
 */
class UserDTOEntity{
    private string $name;
    private ?string $paternalSurname;
    private ?string $maternalSurname;
    private string $email;
    private string $password;

    /***
     * Constructor de la clase User
     * 
     * @param Name Nombre completo del usuario
     * @param PaternalSurname Apellido paterno del ususario
     * @param MaternalSurname Apellido materno del usuario
     * @param Email Email del usuario
     * @param Password Contraseña del usuario
     */
    function __construct(
        string $name, 
        ?string $paternalSurname=null,
        ?string $maternalSurname=null,
        string $email,
        string $password
    ){
        $this->name = $name;
        $this->paternalSurname = $paternalSurname;
        $this->maternalSurname = $maternalSurname;
        $this->email = $email;
        $this->password = $password;
    }

    /*==================================
                Getters
      ==================================*/
    public function getName(){
        return $this->name;
    }

    public function getPaternalSurname(){
        return $this->paternalSurname;
    }
    
    public function getMaternalSurname(){
        return $this->maternalSurname;
    }
    
    public function getEmail(){
        return $this->email;
    }

    public function getPassword(){
        return $this->password;
    }

}

class UserDTOPublic{
    private string $fullName;
    private string $email;

    public function __construct(
        string $fullname,
        string $email,
    )
    {
        $this->fullName = $fullname;
        $this->email = $email;
    }
    
    /*==================================
                Getters
      ==================================*/

    public function getFullName(){
        return $this->fullName;
    }

    public function getEmail(){
        return $this->email;
    }
}

class UserDTOLogin{
    private string $email;
    private string $password;

    public function __construct(
        string $email,
        string $password
    ){
        $this->email = $email;
        $this->password = $password;
    }

    /*==================================
                Getters
      ==================================*/
    public function getEmail(){
        return $this->email;
    }

    public function getPassword(){
        return $this->password;
    }
}
?>