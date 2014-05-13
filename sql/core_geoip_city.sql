CREATE  TABLE core_geoip_city (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    city_name TEXT NOT NULL DEFAULT ''
    country_id INT(11) NOT NULL DEFAULT 0,
    location_id INT(11) NOT NULL DEFAULT 0,
    metro_code INT(11) NOT NULL DEFAULT 0,
    time_zone TEXT NOT NULL DEFAULT ''
    PRIMARY KEY (id)
);

CREATE INDEX country_id_idx ON core_geoip_country (country_id) USING BTREE;
CREATE INDEX network_ip_id_idx ON core_geoip_country (network_ip_id) USING BTREE;
CREATE INDEX city_name_idx ON core_geoip_country (city_name) USING BTREE;
