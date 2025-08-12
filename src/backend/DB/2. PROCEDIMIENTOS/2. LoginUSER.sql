USE TrackTracer;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_AuthenticateLogin
AUTOR                 :       RICARDO-K
FECHA CREACION        :       04/08/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "Autenticar el Login de un Usuario"
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_AuthenticateLogin
	@Email VARCHAR(100), --NOT NULL
	@HashPassword VARCHAR(256), --NOT NULL
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	--Inicializamos con valores por defecto
	SET @StatusCode = 500;
	SET @StatusMessage = 'Ocurrió un error en el servidor. Inténtelo más tarde.';

	BEGIN TRY
		--Validación inicial de los datos
		IF @Email IS NULL OR TRIM(@Email) = '' OR @HashPassword IS NULL OR TRIM(@HashPassword) = ''
			BEGIN
				SET @StatusCode = 400;
				SET @StatusMessage = 'El Email y/o password no fueron ingresados.';
				RETURN;
			END

		BEGIN
			--Inicializamos las variables necesarias
			DECLARE @Id_User INT;
			DECLARE @ActualHashPassword VARCHAR(256);

			--Ejecutamos la consulta
			SELECT 
				@Id_User = Id_User,
				@ActualHashPassword = Password
			FROM [LoginUser]
			WHERE Email = @Email;
			
			--Validamos las credenciales
			IF @Id_User IS NOT NULL AND @HashPassword = @ActualHashPassword
				BEGIN
					SET @StatusCode = 200;
					SET @StatusMessage = 'Autenticación exitosa.';
				END

			--Enviamos un mensaje de error
			ELSE
				BEGIN
					SET @StatusCode = 401;
					SET @StatusMessage = 'Email y/o password incorrectos.';
				END
		END
	END TRY
	BEGIN CATCH
		--Enviamos un mensaje de error
		SET @StatusCode = 500;
		SET @StatusMessage = 'Ocurrió un error en el servidor. Inténtelo más tarde.';
	END CATCH
END;
GO

/***********************************************************************************
Entidad Creacion              PROCEDURE
NOMBRE                :       sp_UpdatePassword
AUTOR                 :       RICARDO-K
FECHA CREACION        :       04/08/2025
MOTIVO                :       GENERAR LR PROCEDIMIENTO ALMACENADO PARA "Actualizar la Contraseña del Usuario"
**********************************************************************************/
CREATE OR ALTER PROCEDURE sp_UpdatePassword
	@Id_User INT,
	@Email VARCHAR(100),
	@CurrentPassword VARCHAR(256),
	@NewPassword VARCHAR(256),
	@StatusCode INT OUTPUT,
	@StatusMessage VARCHAR(MAX) OUTPUT
AS
BEGIN
	--Enviamos un mensaje de error
	SET @StatusCode = 500;
	SET @StatusMessage = 'Ocurrió un error en el servidor. Inténtelo más tarde.';

	BEGIN TRY
		--Validación inicial de los datos
		IF @Id_User IS NULL OR @Id_User < 1
			BEGIN
				SET @StatusCode = 400;
				SET @StatusMessage = 'Identificador de usuario incorrecto.';
				RETURN;
			END

		IF @Email IS NULL OR TRIM(@Email) = ''
			BEGIN
				SET @StatusCode = 400;
				SET @StatusMessage = 'El Email proporcionado no puede estar vacío.';
				RETURN;
			END
		
		IF @CurrentPassword IS NULL OR TRIM(@CurrentPassword) = '' OR @NewPassword IS NULL OR TRIM(@NewPassword) = ''
			BEGIN
				SET @StatusCode = 400;
				SET @StatusMessage = 'Las contraseñas proporcionadas no pueden estar vacías.';
				RETURN;
			END

		--Verificamos las credenciales (Valores genuinos)
		DECLARE @ActualEmail VARCHAR(100);
		DECLARE @ActualPassword VARCHAR(256);

		SELECT
			@ActualEmail = Email,
			@ActualPassword = Password
		FROM [LoginUser]
		WHERE Id_User = @Id_User;

		IF @ActualEmail = @Email AND @ActualPassword = @CurrentPassword
			BEGIN
				UPDATE LoginUser
				SET
					Password = @NewPassword,
					ModifiedAt = GETDATE()
				WHERE Id_User =   @Id_User;

				SET @StatusCode = 200;
				SET @StatusMessage = 'La contraseña se cambió exitosamente.';
			END
		ELSE
			BEGIN
				SET @StatusCode = 401;
				SET @StatusMessage = 'Email y/o password incorrectos.';
			END
	END TRY
	BEGIN CATCH
		--Enviamos un mensaje de error
		SET @StatusCode = 500;
		SET @StatusMessage = 'Ocurrió un error en el servidor. Inténtelo más tarde.';
	END CATCH
END;
GO