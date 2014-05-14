CREATE  TABLE core_geoip_city (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    name VARCHAR(255) NOT NULL DEFAULT '',
    country_id INT(11) NOT NULL DEFAULT 0,
    division_id INT(11) NOT NULL DEFAULT 0,
    postal_code VARCHAR(128) NOT NULL DEFAULT '',
    metro_code VARCHAR(128) NOT NULL DEFAULT 0,
    time_zone VARCHAR(128) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

CREATE INDEX name_idx ON core_geoip_city (name) USING BTREE;
CREATE INDEX country_id_idx ON core_geoip_city (country_id) USING BTREE;
CREATE INDEX division_id_idx ON core_geoip_city (division_id) USING BTREE;
CREATE INDEX postal_code_idx ON core_geoip_city (postal_code) USING BTREE;
CREATE INDEX metro_code_idx ON core_geoip_city (metro_code) USING BTREE;
CREATE INDEX time_zone_idx ON core_geoip_city (time_zone) USING BTREE;

