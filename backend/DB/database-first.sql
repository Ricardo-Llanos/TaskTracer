--===============================================================================
--Crear la Base de Datos
USE master;
CREATE DATABASE TrackTracer;


--===============================================================================
--Crear las tablas
USE TrackTracer;
IF OBJECT_ID('Task') IS NOT NULL
    DROP TABLE Task;

CREATE TABLE Task(
    Id_Task INT IDENTITY(1,1),
    Description TEXT NOT NULL, --Name currently
    Status VARCHAR(20) NOT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(),
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE(),

    PRIMARY KEY (Id_Task)
);

--Cambiar tipo de dato de columna
ALTER TABLE Task
ALTER COLUMN Description VARCHAR (200) NOT NULL;


--Cambiar el nombre de la columna
-- EXEC sp_renam 'Table.NameColumn', 'newName', 'COLUMN';
EXEC sp_rename 'Task.Nombre', 'Name', 'COLUMN';


--Agregar las restricciones
ALTER TABLE Task
ADD CONSTRAINT DF_Task_Status
DEFAULT 'To Do'
FOR Status;

ALTER TABLE Task
ADD CONSTRAINT CK_Task_Status
CHECK (Status in ('To do','In Progress','Done'));

ALTER TABLE Task
ADD CONSTRAINT UQ_Task_Description
UNIQUE(Description); --La restricción UNIQUE no puede ser impuesta a un tipo de dato TEXT

--==========================================
--Debido al cambio de nombre
ALTER TABLE Task 
DROP CONSTRAINT UQ_Task_Description;


ALTER TABLE Task
ADD CONSTRAINT UQ_Task_Name
UNIQUE (Name);
