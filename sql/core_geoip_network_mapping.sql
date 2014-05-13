CREATE  TABLE core_geoip_network_mapping (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    city_id INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX city_id_idx ON core_geoip_country (city_id) USING BTREE;
