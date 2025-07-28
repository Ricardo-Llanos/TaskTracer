<?php

class User{
    private string $Name;
    private ?string $PaternalSurname;
    private ?string $MaternalSurname;
    private string $Email;
    private string $Password;
    private ?string $CreatedAt;
    private ?string $ModifiedAt;

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
        string $Name, 
        ?string $PaternalSurname=null,
        ?string $MaternalSurname=null,
        string $Email,
        string $Password
    )
    {
        $this->Name = $Name;
        $this->PaternalSurname = $PaternalSurname;
        $this->MaternalSurname = $MaternalSurname;
        $this->Email = $Email;
        $this->Password = $Password;
        $this->CreatedAt = null;
        $this->ModifiedAt = null;
    }

    /*==================================
                Getters
      ==================================*/
    public function getName(): string{
        return $this->Name;
    }

    public function getPaternalSurname(): ?string{
        return $this->PaternalSurname;
    }

    public function getMaternalSurname(): ?string{
        return $this->MaternalSurname;
    }

    public function getEmail(): string{
        return $this->Email;
    }

    public function getPassword(): string{
        return $this->Password;
    }

    public function getCreatedAt(): ?string{
        return $this->CreatedAt;
    }

    public function getModifiedAt(): ?string{
        return $this->ModifiedAt;
    }

    /*==================================
                Setters
      ==================================*/
    public function setName(string $Name){
        $Name = trim($Name);

        if ($Name == '' || $Name == null){
            print("setName::El nombre ingresado no es correcto: "+$Name);
            return;
        }

        $Name = ucwords(strtolower($Name)); //EL inicio del nombre será en mayúsculas
        $this->Name = $Name;
    }

    public function setPaternalSurname(string $PaternalSurname){
        $PaternalSurname = trim($PaternalSurname);

        if ($PaternalSurname == '' || $PaternalSurname == null){
            print("setPaternalSurname::El nombre ingresado no es correcto: " + $PaternalSurname);
        }

        $PaternalSurname = ucwords(strtolower($PaternalSurname));
        $this->PaternalSurname = $PaternalSurname;
    }

    public function setMaternalSurname(string $MaternalSurname){
        $MaternalSurname = trim($MaternalSurname);

        if ($MaternalSurname == '' || $MaternalSurname == null){
            print("setMaternalSurname::El nombre ingresado no es correcto: " + $MaternalSurname);
        }

        $MaternalSurname = ucwords(strtolower($MaternalSurname));
        $this->MaternalSurname = $MaternalSurname;
    }

    public function setEmail(string $Email){
        $Email = trim($Email);

        if ($Email == '' || $Email == null){
            print("setEmail::El nombre ingresado no es correcto: " + $Email);
        }

        $Email = ucwords(strtolower($Email));
        $this->Email = $Email;
    }

    public function setPassword(string $Password){
        $Password = trim($Password);

        //Incluir expresiones regulares para verificar que la contraseña sea fuerte

        if ($Password == '' || $Password == null){
            print("setPassword::La contraseña ingresada no es correcto: " + $Password);
        }

        //Añadir el hasheo de contraseña
        $this->Password = $Password;
    }

    public function setCreatedAt(string $CreatedAt){
        return $this->CreatedAt;
    }

    public function setModifiedAt(string $ModifedAt){
        return $this->ModifiedAt;
    }
}
?>