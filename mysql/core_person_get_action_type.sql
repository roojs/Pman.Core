
DROP FUNCTION IF EXISTS core_person_get_action_type;

DELIMITER $$
CREATE FUNCTION core_person_get_action_type(in_id INT(11))  RETURNS VARCHAR(32) DETERMINISTIC
    BEGIN
        
        DECLARE v_ret VARCHAR(32);
        SET v_ret= '';
        SELECT action_type INTO v_ret  FROM core_person WHERE id = in_id  LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ;
