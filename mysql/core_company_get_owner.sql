DROP FUNCTION IF EXISTS company_get_owner;

DELIMITER $$
CREATE FUNCTION company_get_owner()  RETURNS INT(11) DETERMINISTIC
    BEGIN
        DECLARE v_id INT(11);
        SET v_id = 0;
        SELECT id INTO v_id  FROM Companies WHERE isOwner = 1 LIMIT 1;
        RETURN v_id;
    END $$
DELIMITER ;

