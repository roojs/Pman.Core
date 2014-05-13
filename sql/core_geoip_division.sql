CREATE  TABLE core_geoip_division (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    division_code TEXT NOT NULL DEFAULT '',
    division_name TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

CREATE INDEX city_id_idx ON core_geoip_country (city_id) USING BTREE;
CREATE INDEX latitude_idx ON core_geoip_country (latitude) USING BTREE;
CREATE INDEX longitude_idx ON core_geoip_country (longitude) USING BTREE;
