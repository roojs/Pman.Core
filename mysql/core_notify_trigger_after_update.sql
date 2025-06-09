

-- updating of pressrelease_notify causes updates of email*_fails

 
DROP TRIGGER IF EXISTS core_notify_trigger_after_update;
DROP TRIGGER IF EXISTS core_notify_trigger_after_update_crm; -- old version - not used anymore

DELIMITER $$
 
CREATE TRIGGER core_notify_trigger_after_update AFTER UPDATE on core_notify
    FOR EACH ROW
    BEGIN
    
        DECLARE v_is_failure INT;
        DECLARE v_new_fails INT;

        
    
        -- make sure that act_start does not get modified if sent is set.
        IF (OLD.sent IS NOT NULL AND  OLD.sent > '1500-01-01 00:00:00')
            AND  ( NEW.act_start != OLD.act_start  OR NEW.act_when != OLD.act_when )  THEN
                UPDATE `Error: Can not update core_notify action dates  after its sent` SET x = 1;
        END IF;
        
        
        -- crm support on crm_person (in here as we cant support muliple triggers..
        
        IF
            OLD.person_table = 'crm_person'
            AND
            OLD.crm_person_id > 0
        THEN
             
            IF
                OLD.reject_match_id != NEW.reject_match_id
                AND
                NEW.reject_match_id > 0
              
                        
            THEN
                SELECT
                    is_failure into v_is_failure
                FROM
                    mail_reject_match
                WHERE
                    id = NEW.reject_match_id
                LIMIT 1;
                
                IF     
                    v_is_failure = 1
                THEN
            
                    SELECT 
                        COALESCE(SUM(CASE WHEN reject_match_id = NEW.reject_match_id THEN 1 ELSE 0 END ), 0) INTO
                            v_new_fails
                        FROM (
                            SELECT 
                                reject_match_id 
                            FROM 
                                core_notify 
                            WHERE
                                crm_person_id =  NEW.crm_person_id  
                            AND
                                person_table = 'crm_person'
                            AND
                                id <= NEW.id
                            AND
                                sent > NOW() - INTERVAL 1 YEAR
                            ORDER BY 
                                id DESC 
                            LIMIT  
                               12 
                        ) a;
    
                 
                        UPDATE crm_person set email_fails = v_new_fails WHERE id = NEW.crm_person_id;
                        
                      
                END IF;
            
            ELSE
                -- delivered (reject is not set at this point)
                IF
                    NEW.msgid != ''
                    AND
                    NEW.event_id > 0
                    AND
                    NEW.to_email != ''
                    AND
                    NEW.crm_person_id > 0     
                THEN         
                        -- it's not error condition.
                     
                    UPDATE crm_person set email_fails =  0 WHERE id = NEW.crm_person_id;
                  
                END IF;
                
            END IF;
        END IF;                  
    END;
    
$$
    
    
DELIMITER ;
