
DROP FUNCTION IF EXISTS core_cities_merge;
DELIMITER $$
CREATE FUNCTION core_cities_merge()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE done INT DEFAULT FALSE;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE csr CURSOR FOR 
        SELECT iso INTO  FROM meta_location;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

        OPEN csr;
        read_loop: LOOP
        FETCH csr INTO v_counter;

            IF done THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

    END $$
DELIMITER ; 