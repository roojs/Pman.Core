

DROP FUNCTION IF EXISTS core_enum_name;
DELIMITER $$
CREATE FUNCTION core_enum_name(in_id INT(11))
        RETURNS VARCHAR(256) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(256);
        SET ret  = '';
        SELECT name INTO ret FROM core_enum
            WHERE id=in_id LIMIT 1;
        RETURN ret;
    END $$
DELIMITER ;