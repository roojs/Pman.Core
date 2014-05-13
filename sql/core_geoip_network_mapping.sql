CREATE  TABLE core_geoip_network_mapping (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    start_ip TEXT NOT NULL DEFAULT '',
    mask_length INT(11) NOT NUL DEFAULT 0,
    city_id INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX start_ip_idx ON core_geoip_country (start_ip) USING BTREE;
CREATE INDEX mask_length_idx ON core_geoip_country (mask_length) USING BTREE;
CREATE INDEX city_id_idx ON core_geoip_country (city_id) USING BTREE;
