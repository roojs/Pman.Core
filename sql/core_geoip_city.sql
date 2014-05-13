CREATE  TABLE core_geoip_city (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    city_name TEXT NOT NULL DEFAULT ''
    PRIMARY KEY (id)
);

CREATE INDEX city_name_idx ON core_geoip_country (city_name) USING BTREE;
