
DROP FUNCTION IF EXISTS core_project_get_type;
DELIMITER $$
CREATE FUNCTION core_project_get_type(in_id INT(11))  RETURNS VARCHAR(2) DETERMINISTIC
    BEGIN
        DECLARE v_ret VARCHAR(2);
        SET v_ret= '';
        SELECT type INTO v_ret  FROM core_project WHERE id = in_id LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ;