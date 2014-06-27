DROP TABLE IF EXISTS core_geoip_location;

-- CREATE  TABLE core_geoip_location (
--     id INT(11) NOT NULL AUTO_INCREMENT ,
--     latitude DECIMAL(11,8) NOT NULL DEFAULT 0,
--     longitude DECIMAL(11,8) NOT NULL DEFAULT 0,
--     city_id INT(11) NOT NULL DEFAULT 0,
--     PRIMARY KEY (id)
-- );
-- 
-- CREATE INDEX latitude_idx ON core_geoip_location (latitude) USING BTREE;
-- CREATE INDEX longitude_idx ON core_geoip_location (longitude) USING BTREE;
-- CREATE INDEX city_id_idx ON core_geoip_location (city_id) USING BTREE;