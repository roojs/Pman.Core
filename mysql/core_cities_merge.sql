
DROP FUNCTION IF EXISTS core_cities_merge;
DELIMITER $$
CREATE FUNCTION core_cities_merge()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE done INT DEFAULT FALSE;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
        DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;
        DECLARE v_id INT DEFAULT 0;

        DECLARE csr CURSOR FOR 
        SELECT 
            iso,local_name,type,in_location
        FROM 
            meta_location

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO v_iso,v_local_name,v_type,v_in_location;
            
            SET v_id = 0;

            IF v_type = 'CO' THEN
                SELECT id INTO v_id FROM core_geoip_country WHERE code = v_iso;
                IF(id = 0) THEN
                    INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
                END IF;

                ITERATE read_loop;
                
            END IF;

            IF v_type = 'RE' THEN
                SELECT id INTO v_id FROM core_geoip_country WHERE code = v_iso;
                IF(id = 0) THEN
                    INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
                END IF;

                ITERATE read_loop;
                
            END IF;

            IF v_type = 'CI' THEN
                SELECT id INTO v_id FROM core_geoip_country WHERE code = v_iso;
                IF(id = 0) THEN
                    INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
                END IF;

                ITERATE read_loop;
                
            END IF;
                    

            IF done THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

        RETURN v_local_name;
    END $$
DELIMITER ; 

