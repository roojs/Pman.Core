
 
DROP FUNCTION IF EXISTS company_get_by_name;
DELIMITER $$
CREATE FUNCTION company_get_by_name(in_name VARCHAR(128))  RETURNS INT(11) DETERMINISTIC
    BEGIN
        DECLARE v_ret INT(11);
        SET v_ret= 0;
        SELECT id    INTO v_ret  FROM Companies WHERE name = in_name LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ; 