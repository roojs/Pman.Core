CREATE  TABLE core_geoip_division (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    division_code TEXT NOT NULL DEFAULT '',
    division_name TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

CREATE INDEX division_code_idx ON core_geoip_country (division_code) USING BTREE;
CREATE INDEX division_name_idx ON core_geoip_country (division_name) USING BTREE;
