USE TrackTracer;
GO

/*===============================================================================
    VERIFICAR DATOS
=============================================================================== */
DECLARE @MyStatusCode INT;
DECLARE @MyStatusMessage VARCHAR(MAX);
EXEC sp_GetUsers @FilterbyName= 'Juan', @Orderby= 'Pat', @StatusCode=@MyStatusCode OUTPUT, @StatusMessage=@MyStatusMessage OUTPUT;

SELECT @MyStatusCode AS StatusCode, @MyStatusMessage AS StatusMessage;

SELECT * FROM UserT;
SELECT * FROM LoginUser;


