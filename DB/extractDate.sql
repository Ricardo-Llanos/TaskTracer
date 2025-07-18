USE TrackTracer;

IF ('DateInformation') EXITS
    DROP TABLE DateInformation;

CREATE TABLE DateInformation(
    Id_Task INT,
    Name VARCHAR(200) NOT NULL,
    Status VARCHAR (20) NOT NULL,
    DateModified DATETIME NOT NULL,
    Second CHAR(2),
    Minute CHAR(2),
    Hour CHAR(2),    
    Day VARCHAR(10),
    Week CHAR(1),
    Month VARCHAR(10),
    Year SMALLINT
);

ALTER TABLE 