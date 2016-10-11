
 
DROP FUNCTION IF EXISTS core_company_get_by_name;
DELIMITER $$
CREATE FUNCTION core_company_get_by_name(in_name VARCHAR(128))  RETURNS INT(11) DETERMINISTIC
    BEGIN
        DECLARE v_ret INT(11);
        SET v_ret= 0;
        SELECT id    INTO v_ret  FROM core_company WHERE name = in_name LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ; 