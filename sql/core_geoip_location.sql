CREATE  TABLE core_geoip_location (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    city_id INT(11) NOT NULL DEFAULT 0,
    latitude INT(11) NOT NULL DEFAULT 0,
    longitude INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX city_id_idx ON core_geoip_country (city_id) USING BTREE;
CREATE INDEX latitude_idx ON core_geoip_country (latitude) USING BTREE;
CREATE INDEX longitude_idx ON core_geoip_country (longitude) USING BTREE;
