

DROP TRIGGER IF EXISTS core_notify_trigger_after_update;

DELIMITER $$
 

CREATE TRIGGER core_notify_trigger_after_update
            AFTER UPDATE ON core_notify
        FOR EACH ROW
        BEGIN
            -- make sure that act_start does not get modified if sent is set.
            IF (OLD.sent IS NOT NULL AND  OLD.sent > '1500-00-00 00:00:00')
                AND  ( NEW.act_start != OLD.act_start  OR NEW.act_when != OLD.act_when )  THEN
                  UPDATE `Error: Can not update core_notify action dates  after its sent` SET x = 1;
            END IF;
        END;
$$
 

DELIMITER ;

        