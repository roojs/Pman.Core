

-- can not delete 'sent' notifications -- trick is to 'null the sent before deleting...

DROP TRIGGER IF EXISTS core_notify_trigger_after_delete;

DELIMITER $$
 
CREATE TRIGGER core_notify_trigger_after_delete AFTER DELETE on core_notify
    FOR EACH ROW
        BEGIN
            if old.sent is not null AND old.sent > '0001-01-01 00:00:00' THEN 
                UPDATE `Error: Can not delete core_notify after it is sent` SET x = 1;
            END IF;
        END;

$$
 

DELIMITER ;
