<?php

class Cconexion
{

    /*Podemos acceder al puerto de la conexión mediante la configuración TCP/IP 
    vista en SQLServer Configuration Manager.
    
    Aquí debemos tener en cuenta que las reglas de entrada y salida del firewall deben tener agregado a este puerto
    
    Tampoco olvides que el usuario al que te conectes debe tener un rol parecido a sysadmin, y a su vez tener acceso a la base de datos.
    */

    public static function ConnectDB()
    {
        $hostname = "localhost";
        $puerto = "1433"; //Puerto de SQLServer
        $dbname = "TrackTracer";
        $username = "LoginPHP";
        $password = "PHPTest12345";

        try {
            $con = new PDO("sqlsrv:Server=$hostname,$puerto;Database=$dbname", $username, $password);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Esta línea hace que en caso de no poder conectarse se lance una excepción

            echo "Conexión establecida correctamente con: $dbname<br><br>";
        } catch (PDOException $exec) {
            echo "La conexión hacia la base de datos: $hostname, no pudo ser concretada. Error: $exec";
        }

        return $con;
    }
}
