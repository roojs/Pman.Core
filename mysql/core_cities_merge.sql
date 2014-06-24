
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
            iso,local_name,type,in_location
        FROM 
            meta_location
        LIMIT 1;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO ;
            
            IF done THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

        RETURN v_iso;
    END $$
DELIMITER ; 

