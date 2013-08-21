

DROP FUNCTION IF EXISTS person_get_project_id;

DELIMITER $$
CREATE FUNCTION person_get_project_id(  
       in_id INT(11)
    )  RETURNS INT(11) DETERMINISTIC
    BEGIN
        
        DECLARE v_id INT(11);
        SET v_id = 0;
        SELECT project_id INTO v_id FROM Person WHERE id  = in_id LIMIT 1;
        RETURN v_id;
    END $$


DELIMITER ;
