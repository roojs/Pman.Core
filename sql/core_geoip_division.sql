CREATE  TABLE core_geoip_division (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    code VARCHAR(32) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    country_id INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX code_idx ON core_geoip_division (code) USING BTREE;
CREATE INDEX name_idx ON core_geoip_division (name) USING BTREE;
