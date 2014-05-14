CREATE  TABLE core_geoip_division (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    code VARCHAR(32) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

CREATE INDEX code_idx ON core_geoip_division (code) USING BTREE;
CREATE INDEX name_idx ON core_geoip_division (name) USING BTREE;
