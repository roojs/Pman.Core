
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

            IF(v_id = 0) THEN
                INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
            END IF;

--             ITERATE co_loop;
                
            IF co_done THEN
              LEAVE co_loop;
            END IF;

        END LOOP;
        CLOSE co_csr;

        OPEN re_csr;
        re_loop: LOOP
            FETCH re_csr INTO v_iso,v_local_name,v_type,v_in_location;
            
            SET v_id = 0;

            SELECT id INTO v_id FROM core_geoip_division WHERE name = v_local_name;

            IF(v_id = 0) THEN
                IF v_in_location IS NOT NULL THEN
                    SELECT id INTO v_id FROM core_geoip_country WHERE code = v_iso;
                END IF;
                
                INSERT INTO core_geoip_division (code, name, country_id) VALUES (v_iso, v_local_name, v_id);
            END IF;

--             ITERATE re_loop;
                
            IF re_done THEN
              LEAVE re_loop;
            END IF;

        END LOOP;
        CLOSE re_csr;

        RETURN v_local_name;
    END $$
DELIMITER ; 

