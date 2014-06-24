
DROP FUNCTION IF EXISTS core_cities_merge_country;
DELIMITER $$
CREATE FUNCTION core_cities_merge_country()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE co_done INT DEFAULT FALSE;

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
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET co_done = TRUE;


        OPEN co_csr;
        co_loop: LOOP
            FETCH co_csr INTO v_id,v_iso,v_local_name,v_in_location;
            
            SET v_id_tmp = 0;

            SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso;

            IF(v_id_tmp = 0) THEN
                INSERT INTO core_geoip_country (code, name, continent_id) VALUES (v_iso, v_local_name, 0);
            END IF;

--             ITERATE co_loop;
                
            IF co_done THEN
              LEAVE co_loop;
            END IF;

        END LOOP;
        CLOSE co_csr;


        RETURN 'DONE';
    END $$
DELIMITER ; 




DROP FUNCTION IF EXISTS core_cities_merge_division;
DELIMITER $$
CREATE FUNCTION core_cities_merge_division()  RETURNS TEXT DETERMINISTIC
    BEGIN
        DECLARE re_done INT DEFAULT FALSE;

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

        DECLARE re_csr CURSOR FOR 
        SELECT 
            id,iso,local_name,type,in_location
        FROM 
            meta_location
        WHERE
            type = 'RE';
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET re_done = TRUE;

        OPEN re_csr;
        re_loop: LOOP
            FETCH re_csr INTO v_id,v_iso,v_local_name,v_in_location;
            
            SET v_id_tmp = 0;

            SELECT id INTO v_id_tmp FROM core_geoip_division WHERE name = v_local_name;

            IF(v_id_tmp = 0) THEN
                IF v_in_location IS NOT NULL THEN
                    SELECT iso INTO v_iso_tmp, local_name INTO v_local_name_tmp, type INTO v_type_tmp FROM meta_location WHERE id = v_in_location;
                    
                    SELECT id INTO v_id_tmp FROM core_geoip_country WHERE code = v_iso_tmp;

                END IF;
                
                INSERT INTO core_geoip_division (code, name, country_id) VALUES (v_iso, v_local_name, v_id_tmp);
            END IF;

--             ITERATE re_loop;
                
            IF re_done THEN
              LEAVE re_loop;
            END IF;

        END LOOP;
        CLOSE re_csr;

        RETURN 'DONE';
    END $$
DELIMITER ; 



