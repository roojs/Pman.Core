
DROP FUNCTION IF EXISTS core_enum_id_by_name;
DELIMITER $$
CREATE FUNCTION core_enum_id_by_name(in_etype VARCHAR(256), in_name VARCHAR(256))
        RETURNS INT(11) DETERMINISTIC
    BEGIN
        DECLARE ret  INT(11);
        SET ret  = '';
        SELECT id INTO ret FROM core_enum
            WHERE name=in_name AND etype=in_etype LIMIT 1;
        RETURN ret;
    END $$
DELIMITER ;