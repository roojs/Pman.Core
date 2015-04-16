


 

DROP PROCEDURE IF EXISTS core_notify_recur_trigger_core_before_insert_method_id;

DELIMITER $$
 
CREATE PROCEDURE core_notify_recur_trigger_core_before_insert_method_id ( i_id INT)
 BEGIN
    IF (i_id< 1) THEN
        RETURN
    END IF;
    CALL core_enum_trigger_check('core_notify_recur', i_id);
    
    
 END;
$$

DELIMITER ;
