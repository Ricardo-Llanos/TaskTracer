<?php

require_once(".env/config.php");

class Cconexion
{
    /*Podemos acceder al puerto de la conexión mediante la configuración TCP/IP 
    vista en SQLServer Configuration Manager.
    
    Aquí debemos tener en cuenta que las reglas de entrada y salida del firewall deben tener agregado a este puerto
    
    Tampoco olvides que el usuario al que te conectes debe tener un rol parecido a sysadmin, y a su vez tener acceso a la base de datos.
    */

    private static ?PDO $con = null;

    function __construct() {}

    /***
     * 
     */
    protected static function ConnectDB() : PDO
    {
        if (self::$con == null) {
            try {
                if (!defined(SERVER_DESA) || !defined(PORT_DESA) || !defined(DB_CONNECT) || !defined(SESSION_USER) || !defined(SESSION_PASSWORD)) {
                    throw new Exception("Las constantes de la conexión a la base de datos no están definidas.");

                } else {
                    self::$con = new PDO(dsn: "sqlsrv::Server=".SERVER_DESA.",".PORT_DESA.";Database=".DB_CONNECT,
                                            username: SESSION_USER, password: SESSION_PASSWORD);
                    self::$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //En caso de no poder conectarse lanza una excepción
                }

            } catch (PDOException $e) {
                error_log("Error de la conexión a la base de datos {Code: ".$e->getCode()." Message: ".$e->getMessage()); //Loggeamos el error, no lo mostramos
                throw new Exception("No pudo realizarse la conexión a la base de datos. Intente más tarde"); //Mostramos un mensaje genérico al usuario

            } catch(Exception $e){ //Este es el error de las constantes
                error_log("Error en la configuración de la base de datos {Code: ".$e->getCode()." Message: ".$e->getMessage());
                throw $e;
            }
        }
        return self::$con;
    }
}
