
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



DROP FUNCTION IF EXISTS i18n_translate;
DELIMITER $$
CREATE FUNCTION i18n_translate(in_ltype  varchar(1) , in_lkey varchar(8), in_inlang varchar(8)) 
        RETURNS VARCHAR(64) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(64);
        SET ret  = '';
        SELECT lval INTO ret FROM i18n
            WHERE ltype=in_ltype AND lkey=in_lkey and inlang=in_inlang LIMIT 1;
        RETURN ret;
        
    END $$
DELIMITER ;