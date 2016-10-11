

DROP FUNCTION IF EXISTS core_person_get_name;

DELIMITER $$
CREATE FUNCTION core_person_get_name(in_id INT(11))  RETURNS VARCHAR(254) DETERMINISTIC
    BEGIN
        
        DECLARE v_ret VARCHAR(254);
        SET v_ret= '';
        SELECT name INTO v_ret  FROM core_person WHERE id = in_id  LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ;