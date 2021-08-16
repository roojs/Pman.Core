
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
DELIMITER;