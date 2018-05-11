
DROP FUNCTION IF EXISTS core_images_count;

DELIMITER $$
CREATE FUNCTION core_images_count(
    in_ontable VARCHAR(128),
    in_onid INT(11)
)  
RETURNS INT(4)  NOT DETERMINISTIC READS SQL DATA 
    BEGIN

        DECLARE v_ret INT(4);
        
        SELECT COUNT(id) INTO v_ret FROM Images WHERE ontable = in_ontable AND onid = in_onid AND is_deleted = 0;

        RETURN v_ret;

    END $$

DELIMITER ;