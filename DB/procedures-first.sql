/*===============================================================================
VISUALIZAR INFORMACIÓN DE LA DB*/

--Ver las tablas
EXEC sp_tables @table_owner='dbo';

--Ver los procedimientos
EXEC sp_stored_procedures;
EXEC sp_stored_procedures @sp_name = 'Get%'; --Filtro por nombre


EXEC DeleteTask @Id_Task=4;
EXEC GetAllTasks;
--===============================================================================
--PROCEDIMIENTOS

/*SI QUEREMOS EJECUTAR TODOS LOS PROCEDIMIENTOS DE GOLPE TENEMOS QUE INCLUIR A UNA INSTRUCCIÓN
      DE CONTROL DE LOTE QUE PERMITA A SSMS EJECUTAR MÚLTIPLES INSTRUCCIONES EN UNA SOLA EJECUCIÓN

Esta instrucción es "GO". Esta instrucción hace que SSMS detecte el final de una instrucción para que así
pueda ejecutarlo.

El "GO" no es necesario a menos que combines una gran cantidad de instrucciones. En caso de no usarse puede saltar
un error como el siguiente:
        "Msg 156, Level 15, State 1, Procedure DeleteTask, Line 23 [Batch Start Line 80]
        Incorrect syntax near the keyword 'PROCEDURE'.
        Msg 156, Level 15, State 1, Procedure DeleteTask, Line 30 [Batch Start Line 80]
        Incorrect syntax near the keyword 'PROCEDURE'."

"GO" debe ponerse al final de una instrucción, si tienes múltiples procedimientos:
    PROCE 1
    PROCE 2
    PROCE 3

Deberás poner el "GO" al final de la ejecución de cada uno (Fuera de toda la sentencia abarcada por BEGIN END)
    PROCE 1
    "GO"
    PROCE 2
    "GO"
    PROCE 3
    "GO"

*/

--Si en caso te equivocaras con el nombre de un Procedimiento, puedes hacer lo siguiente
-- EXEC sp_rename 'dbo.GestAllTasks', 'GetAllTasks';

/*Este procedimiento almacenado debe ser usado con pinzas. Lo único que busca es mantener la estética y
"belleza" de los IDs, al mantenerlos de manera secuencial, pero debes tener cuidado, casi siempre es mejor dejar los huecos de IDs en el sistema*/
CREATE OR ALTER PROCEDURE ReindexId
AS
BEGIN
	SET NOCOUNT ON;
	DECLARE @i INT;
	SELECT @i = COUNT(*) FROM Task;
	SET @i = @i-1;

	IF (@i>0)
		BEGIN
		-- Necesitas la tabla, el método (reseed), y el número al cual lo reindexarás (Este número debe ser -1 al siguiente index)
		DBCC CHECKIDENT (Task, reseed, @i);
		END
END;
GO


--Inserción
--Debido a que no necesitamos que la app se detenga no generaremos un throw
CREATE OR ALTER PROCEDURE InsertTask
    @Name VARCHAR(200),
    @Status VARCHAR(20) = NULL

AS
BEGIN
    SET NOCOUNT ON; --Esta instrucción evita que SQLServer imprima el mensaje "X rows affected"

    DECLARE @StatusCode INT;
    DECLARE @StatusMessage VARCHAR(100); --Está prohibido usar valores como text, ntext o image en variables locales;
    DECLARE @InsertedName VARCHAR(200) = NULL;

    IF (@Name IS NULL)
        BEGIN
            -- THROW 50000, 'No se ingresó ningún parámetro', 1;
            SET @StatusCode = 1;
            SET @StatusMessage = 'No se insertó el nombre de la tarea.';
        END
    ELSE IF ((SELECT 1 FROM Task WHERE Name = @Name) IS NOT NULL)
		BEGIN
			-- THROW 50000, 'Se intentó ingresar una tarea que ya existe', 1;
            SET @StatusCode = 2;
            SET @StatusMessage = 'Se intentó ingresar una Tarea que ya existe';
		END
	ELSE
        BEGIN
            INSERT INTO Task (Name, Status, CreatedAt, ModifiedAt)
                VALUES 
                (@Name, DEFAULT, DEFAULT, DEFAULT);
            
            SET @StatusCode = 0;
            SET @StatusMessage = 'Tarea ingresada correctamente!';
            SET @InsertedName = @Name;
        END
    
    SELECT StatusCode=@StatusCode, StatusMessage=@StatusMessage, InsertedName=@InsertedName;
END;
GO -- CONTROL DE LOTE

--Edición
CREATE OR ALTER PROCEDURE SetTask
    @Id_Task INT,
    @Name TEXT = NULL,
    @Status VARCHAR(20) = NULL --Al poner el NULL hacemos que este parámetro pueda ser opcional

AS
BEGIN
    SET NOCOUNT ON; --Esta instrucción evita que SQLServer imprima el mensaje "X rows affected"
	IF ((SELECT 1 FROM Task WHERE Id_Task = @Id_Task) IS NULL)
		BEGIN
			THROW 50000, 'El Id ingresado no corresponde a ninguna tarea regstrada.', 1;
		END
    ELSE IF (@Name IS NULL AND @Status IS NULL)
        BEGIN
            THROW 50000, 'Ningún parámetro contiene información.', 1;
        END
    ELSE
        BEGIN
            UPDATE Task SET
                Name = COALESCE(@Name, Name), --"COALESCE" Devuelve el primer valor no nulo
                Status = COALESCE(@Status, Status),
                ModifiedAt = GETDATE()
            WHERE Id_Task = @Id_Task;
        END
END;
GO -- CONTROL DE LOTE


--Eliminación
CREATE OR ALTER PROCEDURE DeleteTask --OR ALTER hará que en caso de existir se modifique
    @Id_Task INT
AS
BEGIN
    SET NOCOUNT ON;
    IF (@Id_Task IS NULL)
        BEGIN
            THROW 50000, 'No se ingresó el identificador de la Tarea', 1;
        END
    
    IF NOT EXISTS (SELECT 1 FROM Task WHERE Id_Task = @Id_Task)
        BEGIN
            THROW 50000, 'Se intentó eliminar una tarea que no existe', 1;
        END

	DELETE FROM Task WHERE Id_Task = @Id_Task;
	EXEC ReindexId;
END;
GO --CONTROL DE LOTE


--Retorno
CREATE OR ALTER PROCEDURE GetAllTasks
AS
BEGIN 
    SELECT * FROM Task;
END;
GO --CONTROL DE LOTE

CREATE OR ALTER PROCEDURE GetTask
    @Id_Task INT
AS
BEGIN
    SELECT * FROM Task 
        WHERE Id_Task = @Id_Task;
END;
GO --CONTROL DE LOTE ("Aquí no sería obligatorio, porque no hay más información, pero un archivo siempre está abierto a la adición de contenido")