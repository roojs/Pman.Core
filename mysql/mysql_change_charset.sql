

DROP PROCEDURE IF EXISTS mysql_change_charset;

delimiter $$


CREATE PROCEDURE mysql_change_charset(mytb TEXT)
    BEGIN
    DECLARE mydb TEXT;
    
    SELECT database() INTO mydb;
    
    
    SELECT
        IF(
            csname='utf8' AND collatename='utf8_unicode_ci',
            CONCAT('SELECT ''is Already utf8 as No Need to Convert'''),
            CONCAT('ALTER TABLE ',mytb,' CONVERT TO CHARACTER SET  utf8 COLLATE utf8_unicode_ci')
            
        )
    INTO
        @ConvertEngineSQL
        
    FROM (
        SELECT
            CCSA.character_set_name csname,
            CCSA.collation_name collatename
            FROM
                information_schema.`TABLES` T,
                information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
            WHERE
                CCSA.collation_name = T.table_collation
                AND
                T.table_schema = mydb COLLATE utf8_unicode_ci
                AND
                T.table_name = mytb COLLATE utf8_unicode_ci

    
    ) AA;
            
    -- SELECT ConvertEngineSQL; -- ???
    PREPARE st FROM @ConvertEngineSQL;
    EXECUTE st;
    DEALLOCATE PREPARE st;

END;

$$

DELIMITER ;