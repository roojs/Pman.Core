
DROP PROCEDURE IF EXISTS core_enum_trigger_check;

DELIMITER $$
 
CREATE PROCEDURE core_enum_trigger_check (i_etype VARCHAR(128) , i_id INT)
 BEGIN
    DECLARE v_cnt INT;
    SET v_cnt = 0;
    -- should this check active???
    
    SELECT count(*) INTO v_cnt FROM core_enum WHERE etype = i_etype AND  id = i_id;
    
    IF (v_cnt < 1) THEN
        UPDATE `Core Enum Does not exist` SET x = 1;
    END IF;
 END;
$$

DELIMITER ;

