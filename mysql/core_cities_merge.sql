
DROP FUNCTION IF EXISTS core_cities_merge;
DELIMITER $$
CREATE FUNCTION core_cities_merge()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE done INT DEFAULT FALSE;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
        DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;

        DECLARE csr CURSOR FOR 
        SELECT 
            iso INTO v_iso,
            local_name INTO v_local_name,
            type INTO v_type,
            in_location INTO v_in_location
        FROM 
            meta_location
        LIMIT 1;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

        OPEN csr;
        read_loop: LOOP
            FETCH csr;
            
            IF done THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

        RETURN v_iso;
    END $$
DELIMITER ; 

