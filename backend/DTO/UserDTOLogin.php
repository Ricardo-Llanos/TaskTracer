<?php
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