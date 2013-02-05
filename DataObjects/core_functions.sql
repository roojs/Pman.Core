

DROP FUNCTION IF EXISTS i18n_translate;
DELIMITER $$
CREATE FUNCTION i18n_translate(in_ltype  varchar(1) , in_lkey varchar(8), in_inlang varchar(8)) 
        RETURNS VARCHAR(64) DETERMINISTIC
    BEGIN
        DECLARE ret  VARCHAR(64);
--         DECLARE v_id INTEGER
        SET ret  = '';
--         SET v_id = 0;
        SELECT lval  INTO ret FROM i18n
            WHERE ltype=in_ltype AND lkey=in_lkey and inlang=in_inlang LIMIT 1;

        IF NOT FOUND THEN
            
        END IF;
--         if (v_id < 1) THEN
        


        RETURN ret;
        
    END $$
DELIMITER ;

DROP FUNCTION IF EXISTS core_enum_seqmax_update;
DELIMITER $$
CREATE FUNCTION core_enum_seqmax_update( in_etype varchar(128))
        RETURNS INT(11) DETERMINISTIC

BEGIN
        DECLARE v_seqmax INT(11);
        SELECT MAX(seqid) +1 INTO v_seqmax FROM core_enum WHERE
            etype = in_etype;
        UPDATE core_enum SET seqmax = v_seqmax WHERE etype = in_etype;
        RETURN v_seqmax;
    END $$
DELIMITER ;

-- usage: SELECT core_enum_seqmax_update(DISTINCT(etype)) FROM core_enum;
DROP TABLE IF EXISTS core_enum_tmp;
CREATE TEMPORARY TABLE core_enum_tmp SELECT DISTINCT(etype) FROM core_enum;
SELECT core_enum_seqmax_update(etype) FROM core_enum_tmp;
DROP TABLE IF EXISTS core_enum_tmp;