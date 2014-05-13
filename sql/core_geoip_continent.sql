CREATE  TABLE core_geoip_continent (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    continent_code TEXT NOT NULL DEFAULT '',
    continent_name TEXT NOT NULL DEFAULT ''
    PRIMARY KEY (id)
);

CREATE INDEX continent_code_idx ON core_geoip_country (continent_code) USING BTREE;
CREATE INDEX continent_name_idx ON core_geoip_country (continent_name) USING BTREE;
