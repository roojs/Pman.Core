

DROP TRIGGER IF EXISTS core_domain_trigger_after_update;

DELIMITER $$
 

CREATE TRIGGER core_domain_trigger_after_update
            AFTER UPDATE ON core_domain
        FOR EACH ROW
        BEGIN
            IF (NEW.mx_updated != '1000-01-01 00:00:00' AND  NEW.has_mx = 0) THEN
                  UPDATE core_domain set email_fails =  0 WHERE id = NEW.person_id;
            END IF;
        END;
$$
 

DELIMITER ;