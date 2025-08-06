<?php
require_once("backend/API/api_user.php");
require_once("backend/POO/User.php");


print("\n=================== MENÚ DE OPCIONES ===================\n");
print("1. Iniciar Sesión\n");
print("2. Soy un Nuevo Usuario\n");
$option = readline("==================\nEscoje una opción");

switch ($option) {
    case 2:
        print("\n\n======AGREGAR NUEVO USUARIO=======\n");
        $Name = readfile("Ingrese el Nombre\n");
        $PSurname = readfile("Ingrese el Apellido paterno\n");
        $MSurname = readfile("Ingrese el Apellido materno\n");
        $Email = readfile("Ingrese el Email\n");
        $Password = readfile("Ingrese la Contraseña\n");

        $User = new User(Name: $Name, 
                        PaternalSurname: $PSurname,
                        MaternalSurname: $MSurname,
                        Email: $Email,
                        Password: $Password);

        $APIUser = new APIUser();
        $APIUser->insertUser($User);
        break;
}
