
DROP FUNCTION IF EXISTS core_cities_merge;
DELIMITER $$
CREATE FUNCTION core_cities_merge()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE co_done INT DEFAULT FALSE;
        DECLARE re_done INT DEFAULT FALSE;
        DECLARE ci_done INT DEFAULT FALSE;

        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
        DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;
        DECLARE v_id INT DEFAULT 0;

        DECLARE co_csr CURSOR FOR 
        SELECT 
            iso,local_name,type,in_location
        FROM 
            meta_location
        WHERE
            type = 'CO';

        DECLARE re_csr CURSOR FOR 
        SELECT 
            iso,local_name,type,in_location
        FROM 
            meta_location
        WHERE
            type = 'RE';

        DECLARE ci_csr CURSOR FOR 
        SELECT 
            iso,local_name,type,in_location
        FROM 
            meta_location
        WHERE
            type = 'CI';

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET co_done = TRUE;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET re_done = TRUE;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET ci_done = TRUE;


        OPEN co_csr;
        co_loop: LOOP
            FETCH co_csr INTO v_iso,v_local_name,v_type,v_in_location;
            
            SET v_id = 0;

            SELECT id INTO v_id FROM core_geoip_country WHERE code = v_iso;

            IF(v_id > 0) THEN
                INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
            END IF;

--             ITERATE read_loop;
                
            IF done THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

        RETURN v_local_name;
    END $$
DELIMITER ; 

