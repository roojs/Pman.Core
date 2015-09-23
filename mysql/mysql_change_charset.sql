

DROP PROCEDURE IF EXISTS mysql_change_charset;

delimiter $$


CREATE PROCEDURE mysql_change_charset(mytb TEXT)
    BEGIN
    DECLARE mydb TEXT;
    
    SELECT database() INTO mydb;
    
    
    SELECT
        IF(
            dbtb2='.',
            CONCAT('ALTER TABLE ',dbtb1,' ENGINE=InnoDB'),
            CONCAT('SELECT ''',dbtb1,' is Already InnoDB'' as \"No Need to Convert\"')
        )
    INTO
        @ConvertEngineSQL
        
    FROM (
        SELECT
            CONCAT(A.db,'.',A.tb) dbtb1,
            CONCAT(IFNULL(B.db,''),'.',IFNULL(B.tb,'')) dbtb2,engine
        FROM
            (
                SELECT
                    table_schema db,table_name tb,engine
                FROM
                    information_schema.tables
                WHERE
                    table_schema=mydb and table_name=mytb
            ) A
        LEFT JOIN
            (
                SELECT
                    table_schema db,table_name tb
                FROM
                    information_schema.tables
                WHERE
                    table_schema=mydb and table_name=mytb AND engine='InnoDB'
            ) B
        USING
            (db,tb)
    ) AA;
            
    -- SELECT ConvertEngineSQL; -- ???
    PREPARE st FROM @ConvertEngineSQL;
    EXECUTE st;
    DEALLOCATE PREPARE st;

END;

$$

DELIMITER ;