CREATE  TABLE core_geoip_country (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    continent_id INT(11) NOT NULL DEFAULT '',
    country_code TEXT NOT NULL DEFAULT '',
    country_name TEXT NOT NULL DEFAULT ''
    PRIMARY KEY (id)
);

CREATE INDEX continent_id_idx ON core_geoip_country (continent_id) USING BTREE;
CREATE INDEX country_code_idx ON core_geoip_country (country_code) USING BTREE;
CREATE INDEX country_name_idx ON core_geoip_country (country_name) USING BTREE;
