CREATE  TABLE core_geoip_country (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    code TEXT NOT NULL DEFAULT '',
    name TEXT NOT NULL DEFAULT '',
    continent_id INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX code_idx ON core_geoip_country (code) USING BTREE;
CREATE INDEX name_idx ON core_geoip_country (name) USING BTREE;
CREATE INDEX continent_id_idx ON core_geoip_country (continent_id) USING BTREE;
