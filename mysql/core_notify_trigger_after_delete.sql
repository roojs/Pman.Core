

-- can not delete 'sent' notifications -- trick is to 'null the sent before deleting...

DROP TRIGGER IF EXISTS core_notify_trigger_after_delete;

DELIMITER $$
 
CREATE TRIGGER core_notify_trigger_after_delete AFTER DELETE on core_notify
    FOR EACH ROW
        BEGIN
            -- I think we changed it so that sent is not null, deafult '0000....'
            DECLARE v_id INT;
            SET v_id = 0;
            SELECT count(*) INTO v_id FROM core_notify_archive WHERE id = old.id ;
            
            if v_id < 1 AND old.sent is not null AND old.sent > '1500-01-01 00:00:00' THEN 
                UPDATE `Error: Can not delete core_notify after it is sent` SET x = 1;
            END IF;
        END;

$$
 

DELIMITER ;
