

DROP FUNCTION IF EXISTS core_enum_name_to_display_name;
DELIMITER $$
CREATE FUNCTION core_enum_name_to_display_name(in_etype VARCHAR(256), in_name VARCHAR(256))
        RETURNS VARCHAR(256) DETERMINISTIC
    BEGIN
        DECLARE ret VARCHAR(256);
        SET ret  = '';
        SELECT display_name INTO ret FROM core_enum
            WHERE name=in_name AND etype=in_etype LIMIT 1;
        RETURN ret;
    END $$
DELIMITER ;