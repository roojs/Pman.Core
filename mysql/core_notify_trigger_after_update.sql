

DROP TRIGGER IF EXISTS core_notify_trigger_after_update;

DELIMITER $$
 

CREATE TRIGGER core_notify_trigger_after_update
            AFTER UPDATE ON core_notify
        FOR EACH ROW
        BEGIN
            -- make sure that act_start does not get modified if sent is set.
            IF (OLD.sent IS NOT NULL AND  OLD.sent > '1500-01-01 00:00:00')
                AND  ( NEW.act_start != OLD.act_start  OR NEW.act_when != OLD.act_when )  THEN
                  UPDATE `Error: Can not update core_notify action dates  after its sent` SET x = 1;
            END IF;

            IF
                person_table = 'crm_person'
                AND
                crm_person_id > 0
            THEN
                CALL core_notify_crm_update(
                    OLD.reject_match_id,
                    NEW.reject_match_id,
                    NEW.crm_person_id,
                    NEW.id,
                    NEW.msgid,
                    NEW.event_id,
                    NEW.to_email
                );
            END IF;
        END;
$$
 

DELIMITER ;

        