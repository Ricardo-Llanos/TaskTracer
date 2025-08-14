USE TrackTracer;
GO
/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_GetUsers
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR EL PROCEDIMIENTO ALMACENADO PARA "Obtener Todos los Usuarios"
**********************************************************************************/

/*
==============================PARAMETER NULL==============================
Si usamos @Parameter INT NULL, lo que haremos es definir que ese parámetro pueda ser NULL, mas no la estaremos haciendo opcional.

Para hacer opcional a un parámetro debemos hacerlo igual a NULL:
@Parameter INT = NULL, De esta manera el parámetro podrá ser o no incluido al llamar al parámetro y podrá ser NULO al momento de ejecutarlo.

==============================DINAMIC QUERYS==============================
Para manipular datos los cuales necesiten aplicar filtros opcionales o partes de código que puedan variar durante la ejecución es necesario
usar CONSULTAS DINÁMICAS.

Las Consultas Dinámicas son consultas SQL que se van formando con el tiempo. Puedes usarlas directamente en el código ejecutándolas directamente,
pero la manera más segura y óptima de hacerlo es por medio del procedimiento SQLSERVER "sp_executesql", este método exige que el primer parámetro (query)
sea un NVARCHAR. 

============================== OFFSET ==============================
El comando OFFSET es una parte utilizable sobre ORDER BY el cual nos permite implementar la paginación --Establecer una cantidad de filas por página,
la página empezará desde la fila que quieras y contendrá un número determinado de filas--. Offset nos permite especificar: Cuántas filas nos queremos saltar? y
Cuántas filas queremos mostrar después de las que saltamos?

Nota => Si quieres empezar desde el registro 11, debemos avanzar 10 registros y luego mostrar algunos. Para hacer esto podemos definir los ROWS como tal,
o también multiplicar la página por el tamaño de la página.

Pag = 2, Size = 10 => OFFSET (Pag-1)*Size ROWS FETCH NEXT Size ROWS ONLY;

Estructura Básica:
SELECT 
	[Columns] 
FROM 
	[Table]
ORDER BY 
	[Filter]
OFFSET [Páginas a Saltar] ROWS
FETCH NEXT [Número de Registros a mostrar] ROWS ONLY;

*/

/***
* @param int PageNumber -> Página que quieres recuperar del contenido
* @param int PageSize -> Tamaño de la página (Cant. de registros que podrá almacenar)
* @param VARCHAR(MAX) FilterbyName -> 
*/
CREATE OR ALTER PROCEDURE sp_GetUsers
	@PageNumber INT = 1,
	@PageSize INT = 20,
	@FilterbyName VARCHAR(MAX) = NULL,
	@FilterbyPaternalSurname VARCHAR(MAX) = NULL,
	@FilterbyMaternalSurname VARCHAR(MAX) = NULL,
	@FilterbyEmail VARCHAR(MAX) = NULL,
	@Orderby VARCHAR(MAX) = 'PaternalSurname',
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	SET NOCOUNT ON; --Necesario para evitar el mensaje "x rows affected"
	SET @StatusCode = 500;
	SET @StatusMessage = 'GetUsers::Ha ocurrido un error inesperado, no se retornó ningún registro.';

	--Validamos los parámetros de entrada
	IF @PageNumber IS NULL OR @PageNumber < 1
		BEGIN
			SET @PageNumber = 1;
		END
	IF @PageSize IS NULL OR @PageSize<1
		BEGIN
			SET @PageSize = 20;
		END

	IF @Orderby IS NULL OR @Orderby NOT IN ('Id_User', 'Name', 'PaternalSurname', 'MaternalSurname', 'Email')
		BEGIN
			SET @Orderby = 'PaternalSurname';
			--SET @Orderby = 'Id_User';
		END

	--Iniciamos la consulta
	BEGIN TRY
		DECLARE @sql NVARCHAR(MAX);
		DECLARE @Filters NVARCHAR(MAX) = N'';
		DECLARE @Params NVARCHAR(MAX);

		SET @Filters = N'';

		--"N" es necesario para especificar que es NVARCHAR
		SET @sql = N'SELECT 
			U.Id_User,
			U.Name,
			U.PaternalSurname,
			U.MaternalSurname,
			LU.Email
		FROM [UserT] AS U
		INNER JOIN [LoginUser] AS LU 
		ON U.Id_User = LU.Id_User
		
		WHERE 1=1'; --Añadimos esta línea para después agregar más condicionales
		
		/*
		DEFINIMOS LOS FILTROS

		- Si usamos 'LIKE' + '%'<filter>+'%', el valor del filtro proporcionado será buscado en todo el campo.
		- Si usamos 'LIKE' + '%'<filter>, el valor del filtro proporcionado será buscado solo al final del campo
		- Si usamos 'LIKE' + <filter>+'%', el valor del filtro proporcionado será buscado solo al inicio del campo

		- SI usamos 'LEFT' + (<word>, <value>), Se nos devolverá una parte de la cadena en base al "value"
		
		*/
		IF @FilterbyName IS NOT NULL AND TRIM(@FilterbyName) <> ''
			BEGIN
				SET @Filters = @Filters + N' AND U.Name LIKE @FilterbyName + ''%'''; --'' '' la doble comilla simple en un NVARCHAR se interpreta como ' ' 
			END

		IF @FilterbyPaternalSurname IS NOT NULL AND TRIM(@FilterbyPaternalSurname) <> ''
			BEGIN
				SET @Filters = @Filters + N' AND U.PaternalSurname LIKE @FilterbyPaternalSurname + ''%''';
			END

		IF @FilterbyMaternalSurname IS NOT NULL AND TRIM(@FilterbyMaternalSurname) <> ''
			BEGIN
				SET @Filters = @Filters + N' AND U.MaternalSurname LIKE @FilterbyMaternalSurname + ''%''';
			END

		IF @FilterbyEmail IS NOT NULL AND TRIM(@FilterbyEmail) <> ''
			BEGIN
				SET @Filters = @Filters + N' AND LU.Email LIKE @FilterbyEmail + ''%''';
			END

		--Añadir filtros a la Consulta Dinamic
		IF @Filters IS NOT NULL AND TRIM(@Filters) <> ''
			BEGIN
				SET @sql = @sql + @Filters;
			END
			
		--Añadimos el ORDER BY 
		SET @sql = @sql + N' 
		 ORDER BY '+@Orderby+N'
		OFFSET (@PageNumber-1)*@PageSize ROWS
		FETCH NEXT @PageSize ROWS ONLY';

		--=========== Definimos los parámetros para la DINAMIC QUERY (No olvides definir los tipos de dato) ===========
		SET @Params = N'@FilterbyName VARCHAR(MAX), @FilterbyPaternalSurname VARCHAR(MAX), @FilterbyMaternalSurname VARCHAR(MAX), 
		@FilterbyEmail VARCHAR(MAX), @PageNumber INT, @PageSize INT';

		--=========== Ejecutamos el procedimiento ===========
		EXEC sp_executesql @Sql, @Params,
							@FilterbyName = @FilterbyName,
							@FilterbyPaternalSurname = @FilterbyPaternalSurname,
							@FilterbyMaternalSurname = @FilterbyMaternalSurname,
							@FilterbyEmail = @FilterbyEmail,
							@PageNumber = @PageNumber,
							@PageSize = @PageSize;

		SET @StatusCode = 200;
		SET @StatusMessage = 'GetUsers::Solicitud procesada correctamente';
	END TRY
	BEGIN CATCH
		SET @StatusCode = 500;
		SET @StatusMessage = 'GetUsers::Ha ocurrido un error inesperado, no se retornó ningún registro: '+ERROR_MESSAGE();
	END CATCH;
END;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_GetUserbyId
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "ObtenerUsuario por Id"
**********************************************************************************/

CREATE OR ALTER PROCEDURE sp_GetUserbyId
	@Id_User INT,
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	SET NOCOUNT ON;

	IF @Id_User IS NULL
		BEGIN
			SET @StatusCode = 400;
			SET @StatusMessage = 'GetUserbyId::La solicitud no contiene el Identificador del usuario.';
		END
	ELSE
		BEGIN TRY
			SELECT 
				U.Name,
				U.PaternalSurname,
				U.MaternalSurname,
				LU.Email
			FROM [UserT] AS U
			INNER JOIN [LoginUser] AS LU 
			ON U.Id_User = LU.Id_User
			WHERE @Id_User = U.Id_User;

			SET @StatusCode = 200;
			SET @StatusMessage = 'GetUsersbyId::Solicitud procesada correctamente';
		END TRY
		BEGIN CATCH
			SET @StatusCode = 500;
			SET @StatusMessage = 'GetUsersbyId::Ha ocurrido un error inesperado, no se retornó ningún registro';

		END CATCH;
END;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_GetUserbyEmail
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "ObtenerUsuario por Email"
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_GetUserbyEmail
	@Email VARCHAR(100),
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	SET NOCOUNT ON;

	SET @StatusCode = '500';
	SET @StatusMessage = 'Error en el servidor';

	IF TRIM(@Email) = ''  OR @Email = NULL
		BEGIN
			SET @StatusCode = '400';
			SET @StatusMessage = 'El Email no puede estar vacío';

			RETURN;
		END;

	ELSE
		BEGIN
			SELECT 1 
				Id_User,
				Email
			FROM LoginUser 
				WHERE Email = @Email;

			SET @StatusCode = '200';
			SET @StatusMessage = 'Usuario retornado exitosamente';
		END;
END;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_RegisterUser
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR EL PROCEDIMIENTO ALMACENADO PARA "Registrar Usuarios" en las tablas User y LoginUser
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_RegisterUser
    @Name VARCHAR(200),
    @PaternalSurname VARCHAR(100) = NULL,
    @MaternalSurname VARCHAR(100) = NULL,
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
    IF @Email IS NULL OR TRIM(@Email) ='' OR @Password IS NULL OR TRIM(@Password) = '' OR @Name IS NULL OR TRIM(@Email) = ''
        BEGIN
            SET @StatusCode = 400;
			SET @StatusMessage = 'La solicitud no contiene todos los valores obligatorios.';
            -- DEBUG DESARROLLO
			-- SET @StatusMessage = 'InserLogintUser::No se ingresó alguno de los valores obligatorios: 
			-- 				Email=> '+ IS NULL(@Email, 'NULL')+
            --                 'Password=> ******'+
			-- 					'Name => '+IS NULL(@Name, 'NULL');
			RETURN; --Es bueno salir directamente del procedimiento si hubo un error
        END;
	ELSE
		BEGIN TRY
			BEGIN TRANSACTION
				--Inserción en la Tabla de Usuario
				INSERT INTO [UserT] (Name, PaternalSurname, MaternalSurname, CreatedAt, ModifiedAt)
				VALUES 
				(@Name, @PaternalSurname, @MaternalSurname, GETDATE(), GETDATE());

				SET @Id_User = SCOPE_IDENTITY();

				--Inserción en la tabla de LoginUser
				INSERT INTO [LoginUser] (Id_User, Email, Password, ModifiedAt)
				VALUES 
				(@Id_User, @Email, @Password, GETDATE());
			
				--Agregar un nuevo procedimiento el cual sea una transacción y englobe a la inserción en el login y en el usuario
				SET @StatusCode = 200;
				SET @StatusMessage = 'Usuario insertado correctamente.';

				COMMIT TRANSACTION;
		END TRY
		BEGIN CATCH 
			IF @@TRANCOUNT > 0 --Verificamos si hubo un error durante la transacción
				BEGIN
					ROLLBACK TRANSACTION;
				END

			SET @StatusCode = 409;
			SET @StatusMessage = 'Transaction InserUser-LoginUser:: Ocurrió un error durante la transacción, Error: ' + ERROR_MESSAGE()--Añadir el error.
			RETURN;	
		END CATCH;
        
END;
GO



/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_UpdateUser
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
VERSIÓN				  :       3
FECHA MODIFICACIÓN    :       04/08/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "Actualizar Usuarios"
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_UpdateUser
	@Id_User INT,
	@Name VARCHAR(200) = NULL,
    @PaternalSurname VARCHAR(100) = NULL,
    @MaternalSurname VARCHAR(100) = NULL,
    @StatusCode INT OUTPUT,
    @StatusMessage VARCHAR(MAX) OUTPUT
AS 
BEGIN
	SET NOCOUNT ON;

	IF @Id_User < 0 OR (SELECT 1 FROM UserT WHERE Id_User = @Id_User) IS NULL
		BEGIN
			SET @StatusCode = 400;
			SET @StatusMessage = 'No se proporcionó el índice del registro a modificar.';
			RETURN;
		END

	IF TRIM(@Name) = ''
		BEGIN
			SET @Name = NULL;
		END
	IF TRIM(@PaternalSurname) = ''
		BEGIN
			SET @PaternalSurname = NULL;
		END
	IF TRIM(@MaternalSurname) = ''
		BEGIN
			SET @MaternalSurname = NULL
		END

	IF @Name IS NULL AND @PaternalSurname IS NULL AND @MaternalSurname IS NULL
		BEGIN
			SET @StatusCode = 400;
			SET @StatusMessage = 'No se proporcionó ningún valor en la actualización de datos.';

			RETURN;
		END
	
	ELSE
		BEGIN
			UPDATE [UserT]
			SET
				Name = COALESCE(@Name, Name), --COALESCE escogerá al valor que no sea nulo
				PaternalSurname = COALESCE(@PaternalSurname, PaternalSurname),
				MaternalSurname = COALESCE(@MaternalSurname, MaternalSurname), 
				ModifiedAt = GETDATE() --Agregamos la fecha actual de modificación
			WHERE Id_User = @Id_User;

			SET @StatusCode = 200;
			SET @StatusMessage = 'El registro se actualizó con éxito.';

		END
END;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_DeleteUser
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR EL PROCEDIMIENTO ALMACENADO PARA "Eliminar Usuarios"
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_DeleteUser
	@Id_User INT,
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	SET NOCOUNT ON;
	--Verificamos que el índice exista en la tabla
	IF @Id_User < 1 OR NOT EXISTS(SELECT * FROM UserT WHERE Id_User = @Id_User) --En caso de no haber coincidencia el valor será vacío, mas no nulo
		BEGIN
			SET @StatusCode = 400;
			SET @StatusMessage = 'El Identificador de usuario no es válido o el registro no existe';
		END
	ELSE
		--Ejecutamos la consulta
		BEGIN TRY
			BEGIN TRANSACTION
				--Eliminamos el registro en la tabla Login
				DELETE FROM LoginUser
					WHERE [LoginUser].Id_User = @Id_User;

				DELETE FROM UserT
					WHERE [UserT].Id_User = @Id_User;

				

				--Debido al ON CASCADE también se borrarán los demás registros

				SET @StatusCode = 200;
				SET @StatusMessage = 'Usuario eliminado exitosamente.';
			COMMIT TRANSACTION;

		END TRY
		BEGIN CATCH
			IF @@TRANCOUNT > 0
				ROLLBACK TRANSACTION;

			SET @StatusCode = 500;
			SET @StatusMessage = 'Ocurrió un error inesperado al intentar eliminar el Usuario.'+ERROR_MESSAGE();
		END CATCH;
END;
GO