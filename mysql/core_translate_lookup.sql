
DROP FUNCTION IF EXISTS core_translate_lookup;

DELIMITER $$
CREATE FUNCTION core_translate_lookup(
    in_ontable VARCHAR(128),
    in_onid INT(11),
    in_name VARCHAR(128),
    in_default TEXT
)  
RETURNS TEXT NOT DETERMINISTIC READS SQL DATA 
    BEGIN

        DECLARE v_ret TEXT;
        
        
        
        
        SELECT COUNT(id) INTO v_ret FROM Images WHERE ontable = in_ontable AND onid = in_onid AND is_deleted = 0;

        RETURN v_ret;

    END $$

DELIMITER ;