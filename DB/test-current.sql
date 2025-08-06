USE TrackTracer;
GO

/*===============================================================================
    VERIFICAR DATOS
=============================================================================== */
DECLARE @MyStatusCode INT;
DECLARE @MyStatusMessage VARCHAR(MAX);
EXEC GetUsers @FilterbyName='M' , @Orderby= 'Pat', @StatusCode=@MyStatusCode OUTPUT, @StatusMessage=@MyStatusMessage OUTPUT;

SELECT @MyStatusCode AS StatusCode, @MyStatusMessage AS StatusMessage;

SELECT * FROM UserT;
SELECT * FROM LoginUser;



