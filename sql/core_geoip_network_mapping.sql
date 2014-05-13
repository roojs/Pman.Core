CREATE  TABLE core_geoip_network_mapping (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    network_start_ip TEXT NOT NULL DEFAULT '',
    network_mask_length INT(11) NOT NUL DEFAULT 0,
    city_id INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX city_id_idx ON core_geoip_country (city_id) USING BTREE;
