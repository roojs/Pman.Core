
DROP FUNCTION IF EXISTS core_person_get_internal_id;

DELIMITER $$
CREATE FUNCTION core_person_get_internal_id(  
        in_addr VARCHAR(254)
        

    )  RETURNS INT(11) DETERMINISTIC
    BEGIN
        
        DECLARE v_id INT(11);
        DECLARE v_company_id INT(11);
        SET v_id = 0;
        SET v_company_id = 0;
        
        SELECT core_company_get_owner() INTO v_company_id;
        IF v_company_id < 1 THEN
            RETURN 0;
        END IF;
        
        SELECT id INTO v_id FROM core_person WHERE
            company_id = v_company_id
            AND email = in_addr LIMIT 1;
        
        RETURN v_id;
        
    
        
        
    END $$
DELIMITER ;
