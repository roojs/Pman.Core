
DROP FUNCTION IF EXISTS core_cities_merge_country;
DELIMITER $$
CREATE FUNCTION core_cities_merge_country()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE co_done INT DEFAULT FALSE;

        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;

        DECLARE v_id INT DEFAULT 0;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
--         DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;
        DECLARE v_geo_lat INT DEFAULT 0;
        DECLARE v_geo_lng INT DEFAULT 0;

        DECLARE v_id_tmp INT DEFAULT 0;
        DECLARE v_iso_tmp TEXT DEFAULT '';
        DECLARE v_local_name_tmp TEXT DEFAULT '';
        DECLARE v_type_tmp TEXT DEFAULT '';
        DECLARE v_in_location_tmp INT DEFAULT 0;

        DECLARE v_id_tmp_tmp INT DEFAULT 0;

        DECLARE co_csr CURSOR FOR 
        SELECT 
            id,iso,local_name,in_location
        FROM 
            meta_location
        WHERE
            type = 'CO';
        
        SELECT COUNT(id) INTO v_total FROM meta_location WHERE type = 'CO';

        SET v_count = 0;

        OPEN co_csr;
        co_loop: LOOP
            FETCH co_csr INTO v_id,v_iso,v_local_name,v_in_location;
            
            SET v_count = v_count + 1;

            SET v_id_tmp = 0;

            SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso;

            IF(v_id_tmp = 0) THEN
                INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
            END IF;
                
            IF v_count = v_total THEN
              LEAVE co_loop;
            END IF;

        END LOOP;
        CLOSE co_csr;


        RETURN v_count;
    END $$
DELIMITER ; 




DROP FUNCTION IF EXISTS core_cities_merge_division;
DELIMITER $$
CREATE FUNCTION core_cities_merge_division()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE re_done INT DEFAULT FALSE;
        
        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;

        DECLARE v_id INT DEFAULT 0;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
--         DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;
        DECLARE v_geo_lat INT DEFAULT 0;
        DECLARE v_geo_lng INT DEFAULT 0;

        DECLARE v_country_id INT DEFAULT 0;
        DECLARE v_iso_tmp TEXT DEFAULT '';
        DECLARE v_local_name_tmp TEXT DEFAULT '';
        DECLARE v_type_tmp TEXT DEFAULT '';
        DECLARE v_in_location_tmp INT DEFAULT 0;

        DECLARE v_division_id INT DEFAULT 0;

        DECLARE re_csr CURSOR FOR 
        SELECT 
            id,iso,local_name,in_location
        FROM 
            meta_location
        WHERE
            type = 'RE';
        
        SELECT COUNT(id) INTO v_total FROM meta_location WHERE type = 'RE';

        SET v_count = 0;

        OPEN re_csr;
        re_loop: LOOP
            FETCH re_csr INTO v_id,v_iso,v_local_name,v_in_location;
            
            SET v_count = v_count + 1;

            SET v_country_id = 0;
            SET v_division_id = 0;
            SET v_iso_tmp = '';
            SET v_local_name_tmp = '';
            SET v_type_tmp = '';

            IF v_in_location IS NOT NULL
                SELECT iso, local_name, type INTO v_iso_tmp, v_local_name_tmp, v_type_tmp FROM meta_location WHERE id = v_in_location;

                SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso_tmp;
                
            END IF;

            SELECT id INTO v_id_tmp FROM core_geoip_division WHERE name = v_local_name;

            IF(v_id_tmp = 0) THEN
                IF v_in_location IS NOT NULL THEN
                    
                    
                    SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso_tmp;

                END IF;
                
                INSERT INTO core_geoip_division (code, name, country_id) VALUES (v_iso, v_local_name, v_id_tmp);
            END IF;

--             ITERATE re_loop;
                
            IF v_count = v_total THEN
              LEAVE re_loop;
            END IF;

        END LOOP;
        CLOSE re_csr;

        RETURN v_count;
    END $$
DELIMITER ; 



DROP FUNCTION IF EXISTS core_cities_merge_city;
DELIMITER $$
CREATE FUNCTION core_cities_merge_city()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE ci_done INT DEFAULT FALSE;

        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;

        DECLARE v_id INT DEFAULT 0;
        DECLARE v_iso TEXT DEFAULT '';
        DECLARE v_local_name TEXT DEFAULT '';
--         DECLARE v_type TEXT DEFAULT '';
        DECLARE v_in_location INT DEFAULT 0;
        DECLARE v_geo_lat INT DEFAULT 0;
        DECLARE v_geo_lng INT DEFAULT 0;

        DECLARE v_id_tmp INT DEFAULT 0;
        DECLARE v_iso_tmp TEXT DEFAULT '';
        DECLARE v_local_name_tmp TEXT DEFAULT '';
        DECLARE v_type_tmp TEXT DEFAULT '';
        DECLARE v_in_location_tmp INT DEFAULT 0;

        DECLARE v_id_tmp_tmp INT DEFAULT 0;

        DECLARE ci_csr CURSOR FOR 
        SELECT 
            id,iso,local_name,in_location,geo_lat,geo_lng
        FROM 
            meta_location
        WHERE
            type = 'CI';

        SELECT COUNT(id) INTO v_total FROM meta_location WHERE type = 'CI';

        SET v_count = 0;

        OPEN ci_csr;
        ci_loop: LOOP
            FETCH ci_csr INTO v_id,v_iso,v_local_name,v_in_location,v_geo_lat,v_geo_lng;
            
            SET v_count = v_count + 1;

            SET v_id_tmp = 0;
            SET v_id_tmp_tmp = 0;

            SELECT id INTO v_id_tmp FROM core_geoip_city WHERE name = v_local_name LIMIT 1;

            IF(v_id_tmp = 0) THEN
                IF v_in_location IS NOT NULL THEN

                    SELECT iso, local_name, type, in_location INTO v_iso_tmp, v_local_name_tmp, v_type_tmp, v_in_location_tmp FROM meta_location WHERE id = v_in_location;
                    
                    IF v_type_tmp = 'CO' THEN
                        SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso_tmp;
                        
                        INSERT INTO core_geoip_city (name, country_id) VALUES (v_local_name, v_id_tmp);
                    END IF;

                    IF v_type_tmp = 'RE' THEN
                        SELECT id INTO v_id_tmp FROM core_geoip_division WHERE name = v_local_name_tmp;
                        
                        SELECT iso, local_name, type INTO v_iso_tmp, v_local_name_tmp, v_type_tmp FROM meta_location WHERE id = v_in_location_tmp;
                        
                        SELECT id INTO v_id_tmp_tmp FROM core_geoip_country WHERE code = v_iso_tmp;
                        
                        INSERT INTO core_geoip_city (name, country_id, division_id) VALUES (v_local_name, v_id_tmp_tmp, v_id_tmp);
                    END IF;
                    
                END IF;

                IF v_geo_lat IS NOT NULL OR v_geo_lng IS NOT NULL THEN
                    SET v_id_tmp = LAST_INSERT_ID();

                    INSERT INTO core_geoip_location (latitude, longitude, city_id) VALUES (v_geo_lat, v_geo_lng, v_id_tmp);
                END IF;
                
                
            END IF;

            IF v_count = v_total THEN
              LEAVE ci_loop;
            END IF;

        END LOOP;
        CLOSE ci_csr;

        RETURN v_count;
    END $$
DELIMITER ; 


-----------------------------------------------------------------------------------------------------------------


DROP FUNCTION IF EXISTS core_country_locations;
DELIMITER $$
CREATE FUNCTION core_country_locations()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;

        DECLARE v_geoname_id INT DEFAULT 0;
        DECLARE v_continent_code TEXT DEFAULT '';
        DECLARE v_continent_name TEXT DEFAULT '';
        DECLARE v_country_iso_code TEXT DEFAULT '';
        DECLARE v_country_name TEXT DEFAULT '';

        DECLARE v_country_id INT DEFAULT 0;
        DECLARE v_continent_id INT DEFAULT 0;

        DECLARE csr CURSOR FOR 
        SELECT 
            geoname_id,continent_code,continent_name,country_iso_code,country_name
        FROM 
            country_locations;
        
        SELECT COUNT(geoname_id) INTO v_total FROM country_locations;

        SET v_count = 0;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO v_geoname_id,v_continent_code,v_continent_name,v_country_iso_code,v_country_name;
            
            SET v_count = v_count + 1;
            
            SET v_country_id = 0;
            SET v_continent_id = 0;
            
            IF (v_continent_code != '') THEN
                SELECT id INTO v_continent_id FROM core_geoip_continent WHERE code = v_continent_code;

                IF v_continent_id = 0 THEN
                    INSERT INTO core_geoip_continent (code, name) VALUES (v_continent_code, v_continent_name);
                    SET v_continent_id = LAST_INSERT_ID();
                END IF;
                
            END IF;

            IF (v_country_iso_code != '') THEN
                
                SELECT id INTO v_country_id FROM core_geoip_country WHERE code = v_country_iso_code;

                IF v_country_id = 0 THEN
                    INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_country_iso_code, v_country_name, v_continent_id);
                END IF;
                
            END IF;
    
            IF v_count = v_total THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;


        RETURN v_count;
    END $$
DELIMITER ; 


DROP FUNCTION IF EXISTS core_country_blocks;
DELIMITER $$
CREATE FUNCTION core_country_blocks()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;
        
        DECLARE v_geoname_id INT DEFAULT 0;
        DECLARE v_network_start_ip TEXT DEFAULT '';
        DECLARE v_network_mask_length INT DEFAULT 0;
        DECLARE v_is_anonymous_proxy INT DEFAULT 0;
        DECLARE v_is_satellite_provider INT DEFAULT 0;

        DECLARE v_country_iso_code TEXT DEFAULT '';

        DECLARE v_country_id INT DEFAULT 0;

        DECLARE csr CURSOR FOR 
        SELECT 
            network_start_ip,network_mask_length,geoname_id,is_anonymous_proxy,is_satellite_provider
        FROM 
            country_blocks
        WHERE 
                geoname_id != 0 
            AND 
                registered_country_geoname_id != 0 
            AND 
                geoname_id = registered_country_geoname_id
            AND
                network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';
        
        SELECT COUNT(network_start_ip) INTO v_total FROM country_blocks WHERE geoname_id != 0 AND registered_country_geoname_id != 0 AND geoname_id = registered_country_geoname_id AND network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';

        SET v_count = 0;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO v_network_start_ip,v_network_mask_length,v_geoname_id,v_is_anonymous_proxy,v_is_satellite_provider;
            
            SET v_count = v_count + 1;
            
            SET v_country_id = 0;

            SELECT country_iso_code INTO v_country_iso_code FROM country_locations WHERE geoname_id = v_geoname_id;

            IF v_country_iso_code != '' THEN
                SELECT id INTO v_country_id FROM core_geoip_country WHERE code = v_country_iso_code;

                IF v_country_id != 0 THEN
                    INSERT INTO core_geoip_country_network_mapping (start_ip, mask_length, country_id, is_anonymous_proxy, is_satellite_provider) VALUES (SUBSTRING_INDEX(v_network_start_ip,':','-1'), POW(2, 128-v_network_mask_length),v_country_id,v_is_anonymous_proxy,v_is_satellite_provider);
                END IF;

            END IF;
            
            IF v_count = v_total THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;


        RETURN v_count;
    END $$
DELIMITER ; 





DROP FUNCTION IF EXISTS core_city_locations;
DELIMITER $$
CREATE FUNCTION core_city_locations()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;

        DECLARE v_country_iso_code TEXT DEFAULT '';
        DECLARE v_subdivision_iso_code TEXT DEFAULT '';
        DECLARE v_subdivision_name TEXT DEFAULT '';
        DECLARE v_city_name TEXT DEFAULT '';
        DECLARE v_metro_code TEXT DEFAULT '';
        DECLARE v_time_zone TEXT DEFAULT '';

        DECLARE v_country_id INT DEFAULT 0;
        DECLARE v_division_id INT DEFAULT 0;
        DECLARE v_city_id INT DEFAULT 0;

        DECLARE csr CURSOR FOR 
        SELECT 
            country_iso_code,subdivision_iso_code,subdivision_name,city_name,metro_code,time_zone
        FROM 
            city_locations
        WHERE
            subdivision_name != '' OR city_name != '';
        
        SELECT COUNT(geoname_id) INTO v_total FROM city_locations WHERE subdivision_name != '' OR city_name != '';;

        SET v_count = 0;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO v_country_iso_code,v_subdivision_iso_code,v_subdivision_name,v_city_name,v_metro_code,v_time_zone;
            
            SET v_count = v_count + 1;
            
            SET v_country_id = 0;
            SET v_division_id = 0;
            SET v_city_id = 0;
            
            
            IF (v_country_iso_code != '') THEN
                SELECT id INTO v_country_id FROM core_geoip_country WHERE code = v_country_iso_code;
            END IF;
    
            IF v_subdivision_name != '' THEN
                SELECT id INTO v_division_id FROM core_geoip_division WHERE name = v_subdivision_name AND country_id = v_country_id;

                IF v_division_id = 0 THEN
                    INSERT INTO core_geoip_division (code, name, country_id) VALUES (v_subdivision_iso_code, v_subdivision_name, v_country_id);
                    SET v_division_id = LAST_INSERT_ID();
                END IF;
                

            END IF;

            IF v_city_name != '' THEN
                
                SELECT id INTO v_city_id FROM core_geoip_city WHERE name = v_city_name AND country_id = v_country_id AND division_id = v_division_id;
                
                IF v_city_id = 0 THEN
                    INSERT INTO core_geoip_city (name, country_id, division_id, metro_code, time_zone) VALUES (v_city_name, v_country_id, v_division_id, v_metro_code, v_time_zone);

                END IF;

            END IF;

            IF v_count = v_total THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;


        RETURN v_count;
    END $$
DELIMITER ; 



DROP FUNCTION IF EXISTS core_city_blocks_mapping;
DELIMITER $$
CREATE FUNCTION core_city_blocks_mapping()  RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_count INT DEFAULT 0;
        DECLARE v_total INT DEFAULT 0;
        
        
        DECLARE v_geoname_id INT DEFAULT 0;

        DECLARE v_country_id INT DEFAULT 0;
        DECLARE v_divison_id INT DEFAULT 0;
        DECLARE v_city_id INT DEFAULT 0;
        DECLARE v_mapping_id INT DEFAULT 0;
        DECLARE v_country_iso_code TEXT DEFAULT '';
        DECLARE v_subdivision_name TEXT DEFAULT '';
        DECLARE v_city_name TEXT DEFAULT '';

        DECLARE csr CURSOR FOR 
        SELECT 
            DISTINCT(geoname_id)
        FROM 
            city_blocks
        WHERE 
                geoname_id != 0
            AND
                network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';
        
        SELECT COUNT(DISTINCT(geoname_id)) INTO v_total FROM city_blocks WHERE geoname_id != 0  AND network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';

        SET v_count = 0;

        OPEN csr;
        read_loop: LOOP
            FETCH csr INTO v_geoname_id;
            
            SET v_count = v_count + 1;
            
            SET v_country_id = 0;
            SET v_divison_id = 0;
            SET v_city_id = 0;
            SET v_mapping_id = 0;

            SET v_country_iso_code = '';
            SET v_subdivision_name = '';
            SET v_city_name = '';

            SELECT id INTO v_mapping_id FROM city_blocks_mapping WHERE geoname_id = v_geoname_id AND city_id = v_city_id;
            
            IF v_mapping_id = 0 THEN
                SELECT country_iso_code,subdivision_name,city_name INTO v_country_iso_code, v_subdivision_name, v_city_name FROM city_locations WHERE geoname_id = v_geoname_id;

                IF v_country_iso_code != '' THEN
                    SELECT id INTO v_country_id FROM core_geoip_country WHERE code = v_country_iso_code;
                END IF;

                IF v_subdivision_name != '' THEN
                    SELECT id INTO v_divison_id FROM core_geoip_division WHERE name = v_subdivision_name AND country_id = v_country_id;
                END IF;

                SELECT id INTO v_city_id FROM core_geoip_city WHERE name = v_city_name AND country_id = v_country_id AND division_id = v_divison_id;

                IF v_city_id != 0 THEN
                    INSERT INTO city_blocks_mapping (geoname_id, city_id) VALUES (v_geoname_id, v_city_id);
                    
                END IF;

            END IF;
            
            
            IF v_count = v_total THEN
              LEAVE read_loop;
            END IF;

        END LOOP;
        CLOSE csr;

        RETURN v_count;
    END $$
DELIMITER ; 


-- 
-- 
-- DROP FUNCTION IF EXISTS core_city_blocks;
-- DELIMITER $$
-- CREATE FUNCTION core_city_blocks()  RETURNS INT DETERMINISTIC
--     BEGIN
--         DECLARE v_count INT DEFAULT 0;
--         DECLARE v_total INT DEFAULT 0;
--         
--         DECLARE v_network_start_ip TEXT DEFAULT '';
--         DECLARE v_network_mask_length INT DEFAULT 0;
--         DECLARE v_geoname_id INT DEFAULT 0;
--         DECLARE v_latitude DECIMAL(11,8) DEFAULT 0;
--         DECLARE v_longitude DECIMAL(11,8) DEFAULT 0;
--         DECLARE v_is_anonymous_proxy INT DEFAULT 0;
--         DECLARE v_is_satellite_provider INT DEFAULT 0;
-- 
--         
--         DECLARE v_city_id INT DEFAULT 0;
--         DECLARE v_mapping_id INT DEFAULT 0;
-- 
--         DECLARE csr CURSOR FOR 
--         SELECT 
--             network_start_ip,network_mask_length,geoname_id,latitude, longitude, is_anonymous_proxy,is_satellite_provider
--         FROM 
--             city_blocks
--         WHERE 
--                 geoname_id != 0
--             AND
--                 network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';
--         
--         SELECT COUNT(network_start_ip) INTO v_total FROM city_blocks WHERE geoname_id != 0  AND network_start_ip REGEXP '::ffff:[0-9]+.[0-9]+.[0-9]+.[0-9]+$';
-- 
--         SET v_count = 0;
-- 
--         OPEN csr;
--         read_loop: LOOP
--             FETCH csr INTO v_network_start_ip,v_network_mask_length,v_geoname_id,v_latitude,v_longitude,v_is_anonymous_proxy,v_is_satellite_provider;
--             
--             SET v_count = v_count + 1;
--             
--             SET v_city_id = 0;
--             SET v_mapping_id = 0;
-- 
--             SELECT id, city_id INTO v_mapping_id, v_city_id FROM city_blocks_mapping WHERE geoname_id = v_geoname_id;
-- 
--             IF v_mapping_id != 0 THEN
-- 
--                 INSERT INTO core_geoip_location (latitude, longitude, city_id) VALUES (v_latitude, v_longitude, v_city_id);
-- 
--                 INSERT INTO core_geoip_city_network_mapping (start_ip, mask_length, city_id, is_anonymous_proxy, is_satellite_provider) VALUES (SUBSTRING_INDEX(v_network_start_ip,':','-1'), POW(2, 128-v_network_mask_length),v_city_id,v_is_anonymous_proxy,v_is_satellite_provider);
--                 
--             END IF;
--             
--             IF v_count = v_total THEN
--               LEAVE read_loop;
--             END IF;
-- 
--         END LOOP;
--         CLOSE csr;
-- 
--         RETURN v_count;
--     END $$
-- DELIMITER ; 
