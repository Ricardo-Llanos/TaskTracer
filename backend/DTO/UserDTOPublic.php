<?php
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

?>