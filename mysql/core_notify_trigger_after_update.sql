

DROP TRIGGER IF EXISTS core_notify_trigger_after_update;

DELIMITER $$
 

CREATE TRIGGER core_notify_trigger_after_update
            BEFORE UPDATE ON core_notify
        FOR EACH ROW
        BEGIN
           DECLARE mid INT(11);
           IF (@DISABLE_TRIGGER IS NULL AND @DISABLE_TRIGGER_{$tbl} IS NULL ) THEN  
           