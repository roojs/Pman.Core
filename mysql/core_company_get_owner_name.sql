
DROP FUNCTION IF EXISTS company_get_owner_name;
DELIMITER $$
CREATE FUNCTION company_get_owner_name()  RETURNS VARCHAR(254) DETERMINISTIC
    BEGIN
        DECLARE v_ret VARCHAR(254);
        SET v_ret= '';
        SELECT name INTO v_ret  FROM Companies WHERE isOwner = 1 LIMIT 1;
        RETURN v_ret;
    END $$
DELIMITER ;
 