CREATE  TABLE core_geoip_city (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    name TEXT NOT NULL DEFAULT ''
    country_id INT(11) NOT NULL DEFAULT 0,
    division_id INT(11) NOT NULL DEFAULT 0,
    postal_code INT(11) NOT NULL DEFAULT 0,
    metro_code INT(11) NOT NULL DEFAULT 0,
    time_zone TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

CREATE INDEX name_idx ON core_geoip_country (name) USING BTREE;
CREATE INDEX country_id_idx ON core_geoip_country (country_id) USING BTREE;
CREATE INDEX division_id_idx ON core_geoip_country (division_id) USING BTREE;
CREATE INDEX postal_code_idx ON core_geoip_country (postal_code) USING BTREE;
CREATE INDEX metro_code_idx ON core_geoip_country (metro_code) USING BTREE;
CREATE INDEX time_zone_idx ON core_geoip_country (time_zone) USING BTREE;

