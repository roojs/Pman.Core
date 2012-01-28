

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



DROP FUNCTION IF EXISTS core_enum_seqmax;
DELIMITER $$
CREATE FUNCTION core_enum_seqmax( etype varchar(128))
BEGIN
        DECLARE seqmax INT(11);
        SELECT MAX(seqid) +1 INTO seqmax FROM core_enum WHERE
            etype = etype;
        RETURN seqmax;
        
    END $$
DELIMITER ;
