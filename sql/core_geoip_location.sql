CREATE  TABLE core_geoip_location (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    city_id INT(11) NOT NULL DEFAULT 0
    latitude INT(11) NOT NULL DEFAULT 0,
    longitude INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX country_id_idx ON core_geoip_country (country_id) USING BTREE;
CREATE INDEX network_ip_id_idx ON core_geoip_country (network_ip_id) USING BTREE;
CREATE INDEX city_name_idx ON core_geoip_country (city_name) USING BTREE;
