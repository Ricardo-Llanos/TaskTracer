--====================================
--Crear la DB
/***********************************************************************************
Entidad Creacion              DATABASE
NOMBRE                :       TrackTracer
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       GENERAR LA BASE DE DATOS "Track Tracer"
**********************************************************************************/
USE master;
GO
CREATE DATABASE TrackTracer;
GO
USE TrackTracer;

--=====================================
--Crear las tablas

/***********************************************************************************
Entidad Creacion              TABLE
NOMBRE                :       Task
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LA TABLA DE "TASK" Y SUS RESTRICCIONES
**********************************************************************************/
IF OBJECT_ID('Task') IS NOT NULL
    DROP TABLE Task;

CREATE TABLE Task(
    Id_Task INT IDENTITY(1,1),
    Id_User INT NOT NULL, --(FK)
    Name VARCHAR(200) NOT NULL,
    Status VARCHAR(20) NOT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(),
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE(),

    PRIMARY KEY (Id_Task)
);
GO
/*===============================================================================
    CONSTRAINTS
=============================================================================== */
ALTER TABLE Task
ADD CONSTRAINT UQ_Task_Name
UNIQUE (Name);

ALTER TABLE Task
ADD CONSTRAINT CK_Task_Status
CHECK (Status IN ('To Do', 'In Progress', 'Done'));

ALTER TABLE Task
ADD CONSTRAINT DF_Task_Status
DEFAULT 'To Do'
FOR Status;

ALTER TABLE Task
ADD CONSTRAINT CK_Task_CreatedAt
CHECK (CreatedAt >= GETDATE());

ALTER TABLE Task
ADD CONSTRAINT CK_Task_ModifiedAt
CHECK (ModifiedAt >= GETDATE());

GO
/***********************************************************************************
Entidad Creacion              TABLE
NOMBRE                :       User
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LA TABLA DE "USER" Y SUS RESTRICCIONES
**********************************************************************************/

IF OBJECT_ID('User') IS NOT NULL
    DROP TABLE User;

CREATE TABLE User(
    Id_User INT IDENTITY(1,1),
    Name VARCHAR(200) NOT NULL,
    PaternalSurname VARCHAR(100),
    MaternalSurname VARCHAR(100),
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(),
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE(),

    PRIMARY KEY (Id_User)
);


/*===============================================================================
    CONSTRAINTS
=============================================================================== */
ALTER TABLE User
ADD CONSTRAINT CK_User_CreatedAt
CHECK (CreatedAt >= GETDATE());

ALTER TABLE User
ADD CONSTRAINT CK_User_ModifiedAt
CHECK (ModifiedAt >= GETDATE());


/***********************************************************************************
Entidad Creacion              TABLE
NOMBRE                :       User
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LA TABLA DE "USER" Y SUS RESTRICCIONES
**********************************************************************************/

IF OBJECT_ID('LoginUser') IS NOT NULL
    DROP TABLE LoginUser;

CREATE TABLE LoginUser(
    Id_User INT, --(FK)
    Email VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL, --Hash
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE()
);

/*===============================================================================
    CONSTRAINTS
=============================================================================== */
ALTER TABLE LoginUser
ADD CONSTRAINT CK_LoginUser_ModifiedAt
CHECK (ModifiedAt >= GETDATE());



/***********************************************************************************
Entidad Creacion              TABLE
NOMBRE                :       Project
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LA TABLA DE "Project" Y SUS RESTRICCIONES
**********************************************************************************/
IF OBJECT_ID('Project') IS NOT NULL
    DROP TABLE Project;

CREATE TABLE Project(
    Id_Project INT IDENTITY(1,1),
    Id_User INT NOT NULL, --(FK)
    Name VARCHAR(200) NOT NULL,
    Description TEXT,
    Status VARCHAR(20) NOT NULL,
    StartDate DATETIME NOT NULL DEFAULT GETDATE(),
    EndDate DATETIME NOT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(),
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE(),

	PRIMARY KEY(Id_Project)
);

/*===============================================================================
    CONSTRAINTS
=============================================================================== */

ALTER TABLE Project
ADD CONSTRAINT UQ_Project_Name
UNIQUE (Name);

ALTER TABLE Project
ADD CONSTRAINT CK_Project_Status
CHECK (Status IN ('To Do', 'In Progress', 'Done'));

ALTER TABLE Project
ADD CONSTRAINT CK_Project_StartDate
CHECK (StartDate >= GETDATE());

ALTER TABLE Project
ADD CONSTRAINT CK_Project_EndDate
CHECK (EndDate >= GETDATE());

ALTER TABLE Project
ADD CONSTRAINT CK_Project_CreatedAt
CHECK (CreatedAt >= GETDATE());

ALTER TABLE Project
ADD CONSTRAINT CK_Project_ModifiedAt
CHECK (ModifiedAt >= GETDATE());


/***********************************************************************************
Entidad Creacion              TABLE
NOMBRE                :       TaskProject
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LA TABLA DE "TaskProject" Y SUS RESTRICCIONES
**********************************************************************************/
IF OBJECT_ID('TaskProject') IS NOT NULL
    DROP TABLE TaskProject;

CREATE TABLE TaskProject(
    Id_Project INT NOT NULL, --(FK)
    Id_Task INT NOT NULL--(FK)
);

--=====================================
--Llaves Foráneas

/***********************************************************************************
Entidad Creacion              FOREIGN KEYS
NOMBRE                :       Task, User, LoginUser
AUTOR                 :       RICARDO-K
FECHA CREACION        :       24/07/2025
MOTIVO                :       ESTABLECER LAS RELACIONES ENTRE LAS TABLAS DE "Task, User, LoginUser, TaskProject"
**********************************************************************************/

--====== Table TASK ======
ALTER TABLE Task
ADD CONSTRAINT FK_Task_Id_User
FOREIGN KEY (Id_User) REFERENCES User(Id_User);


--====== Table LoginUser ======
ALTER TABLE LoginUser
ADD CONSTRAINT FK_LoginUser_Id_User
FOREIGN KEY (Id_User) REFERENCES User(Id_User);


--====== Table Project ======
ALTER TABLE Project
ADD CONSTRAINT FK_Project_Id_User
FOREIGN KEY (Id_User) REFERENCES User(Id_User);


--====== Table TaskProject ======
ALTER TABLE TaskProject
ADD CONSTRAINT FK_TaskProject_Id_Project
FOREIGN KEY (Id_Project) REFERENCES Project (Id_Project);

ALTER TABLE TaskProject
ADD CONSTRAINT FK_TaskProject_Id_User
FOREIGN KEY (Id_User) REFERENCES User (Id_User);