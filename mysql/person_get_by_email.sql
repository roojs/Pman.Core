
DROP FUNCTION IF EXISTS person_get_by_email;

DELIMITER $$
CREATE FUNCTION person_get_by_email(  
        in_addr VARCHAR(254)
        

    )  RETURNS INT(11) DETERMINISTIC
    BEGIN
        
        DECLARE v_id INT(11);
        SET v_id = 0;
        SELECT id INTO v_id FROM Person WHERE email = in_addr LIMIT 1;
        RETURN v_id;
        
    END $$
DELIMITER ;
