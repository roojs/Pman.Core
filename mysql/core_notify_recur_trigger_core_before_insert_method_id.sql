


 

DROP PROCEDURE IF EXISTS core_notify_recur_trigger_core_before_insert_method_id;
DROP TRIGGER IF EXISTS `core_notify_recur_before_insert_core` ;

DELIMITER $$
 
CREATE PROCEDURE core_notify_recur_trigger_core_before_insert_method_id ( i_id INT)
 BEGIN
    IF (i_id > 0) THEN
        CALL core_enum_trigger_check('core_notify_recur', i_id);
    END IF;
    
 END;
 

-- see if this can be done as a trigger

CREATE DEFINER=`root`@`localhost` TRIGGER `core_notify_recur_before_insert_core`
                BEFORE INSERT ON `core_notify_recur`
    FOR EACH ROW
    
       IF (NEW.method_id > 0) THEN
           CALL core_enum_trigger_check('core_notify_recur', NEW.method_id);
       END IF;
            
    END;
$$

DELIMITER ;
