
DROP FUNCTION IF EXISTS i18n_translate;
DELIMITER $$
CREATE FUNCTION core_enum_display_name(in_id INT(11))
        RETURNS VARCHAR(256) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(256);
        SET ret  = '';
        SELECT display_name INTO ret FROM core_enum
            WHERE id=in_id LIMIT 1;
        RETURN ret;
        
    END $$
DELIMITER ;
