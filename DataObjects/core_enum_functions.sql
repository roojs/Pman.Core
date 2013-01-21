
DROP FUNCTION IF EXISTS i18n_translate;
DELIMITER $$
CREATE FUNCTION core_enum_display_name(id INT(11))
        RETURNS VARCHAR(64) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(64);
        SET ret  = '';
        SELECT lval INTO ret FROM i18n
            WHERE ltype=in_ltype AND lkey=in_lkey and inlang=in_inlang LIMIT 1;
        RETURN ret;
        
    END $$
DELIMITER ;