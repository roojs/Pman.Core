
-- fixes sequencing..

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