
DROP FUNCTION  IF EXISTS companies_owner_create;
    
DELIMITER $$
CREATE FUNCTION companies_owner_create(in_code  VARCHAR(32) , in_name VARCHAR(64)) RETURNS VARCHAR(64) DETERMINISTIC
BEGIN
    DECLARE v_id INT(11);
    SET v_id = 0;
    #// check if person exists..
    SELECT id INTO v_id FROM Companies
        WHERE comptype='OWNER' LIMIT 1;

    IF v_id  != 0 THEN
        RETURN CONCAT('DUPE - Company', in_name);
    END IF;
    
    INSERT INTO Companies (code, name, comptype, comptype_id) VALUES (
        in_code,in_name,'OWNER', core_enum_id_by_name('COMPTYPE', 'OWNER' )
    );
        
    RETURN 'ADDED';
END $$
DELIMITER ;