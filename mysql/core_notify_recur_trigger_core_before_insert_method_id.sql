


 

DROP PROCEDURE IF EXISTS core_notify_recur_trigger_core_before_insert_method_id;
DROP TRIGGER IF EXISTS `core_notify_recur_before_insert_core` ;

DELIMITER $$
 
CREATE PROCEDURE core_notify_recur_trigger_core_before_insert_method_id ( i_id INT)
 BEGIN
    IF (i_id > 0) THEN
        CALL core_enum_trigger_check('core_notify_recur', i_id);
    END IF;
    
 END;
 
$$
-- see if this can be done as a trigger
 

DELIMITER ;
