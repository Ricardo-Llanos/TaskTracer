/***********************************************************************************
Entidad Creacion              TIPOS DE MENSAJE
NOMBRE                :       StatusCode
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       DELIMITAR EL SIGNIFICADO DE CADA CÓDIGO DE ESTATUS
**********************************************************************************/
/*
StatusCode
- 0 => Sin problemas
- 1 => Error en la inserción de datos
- 2 => Error durante un procedimiento
*/



/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       InsertUser
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR EL PROCEDIMIENTO ALMACENADO PARA "Insertar Usuarios" en las tablas User y LoginUser
**********************************************************************************/
CREATE OR ALTER PROCEDURE InsertUser
    @Name VARCHAR(200),
    @PaternalSurname VARCHAR(100) NULL,
    @MaternalSurname VARCHAR(100) NULL,
    @Email VARCHAR(100),
    @Password VARCHAR(255),
    @StatusCode INT OUTPUT,
    @StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	SET NOCOUNT ON; --Para evitar mensajes como el num de filas afectadas
	
	DECLARE @Id_User INT;

	/*LTRIM & RTRIM => Left Trim & Right Trim (Debes usarlo en versiones anteriores a SQLServer 2017)

	- TRIM está disponible desde SQLServer 2017
		- TRIM(string_expression) => elimina caracteres de ambos extremos [Estado por defecto]
		- TRIM(LEADING FROM string_expression) => elimina caracteres de la izquierda
		- TRIM(TRAILING FROM string_expression) => elimina caracteres de la derecha
		- TRIM(BOTH FROM string_expression) => elimina caracteres de ambos extremos

		- TRIM('x' FROM 'xxHelloWorldxx') => Salida: HelloWorld


	*/
    IF @Email IS NULL OR TRIM(@Email) ='' OR @Password IS NULL OR TRIM(@Password) = ''
        BEGIN
            SET @StatusCode = 1;
            SET @StatusMessage = 'InserLogintUser::No se ingresó alguno de los valores obligatorios: 
							Email=> '+ IS NULL(@Email, 'NULL')+
                            'Password=> ******';
			RETURN; --Es bueno salir directamente del procedimiento si hubo un error
        END;
    ELSE IF @Name IS NULL OR TRIM(@Email) = ''
		BEGIN
			SET @StatusCode = 1;
			SET @StatusMessage = 'InsertUser:: No se ingresó alguno de los valores obligatorios: 
								Name => '+IS NULL(@Name, 'NULL');
			RETURN; --Es bueno salir directamente del procedimiento si hubo un error

		END;
	ELSE
		BEGIN TRY
			BEGIN TRANSACTION
				--Inserción en la Tabla de Usuario
				INSERT INTO [User] (Name, PaternalSurname, MaternalSurname, CreatedAt, ModifiedAt)
				VALUES 
				(@Name, @PaternalSurname, @MaternalSurname, GETDATE(), GETDATE());

				SET @Id_User = SCOPE_IDENTITY();

				--Inserción en la tabla de LoginUser
				INSERT INTO [LoginUser] (Id_User, Email, Password, ModifiedAt)
				VALUES 
				(@Id_User, @Email, @Password, GETDATE());
			
				--Agregar un nuevo procedimiento el cual sea una transacción y englobe a la inserción en el login y en el usuario
				SET @StatusCode = 0;
				SET @StatusMessage = 'Usuario insertado correctamente';

				COMMIT TRANSACTION;
		END TRY;
		BEGIN CATCH 
			IF @@TRANCOUNT > 0 --Verificamos si hubo un error durante la transacción
				BEGIN
					ROLLBACK TRANSACTION;
				END

			@StatusCode = 2;
			@StatusMessage = 'Transaction InserUser-LoginUser:: Ocurrió un error durante la transacción, Error: ' + ERROR_MESSAGE()--Añadir el error.
			RETURN;	
		END CATCH;
        
END;



/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       InsertTask
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "Insertar Tareas"
**********************************************************************************/
