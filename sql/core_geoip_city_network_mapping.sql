DROP TABLE IF EXISTS core_geoip_network_mapping;
DROP TABLE IF EXISTS core_geoip_city_network_mapping;

CREATE  TABLE core_geoip_city_network_mapping (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    start_ip VARCHAR(128) NOT NULL DEFAULT '',
    mask_length INT(11) NOT NULL DEFAULT 0,
    city_id INT(11) NOT NULL DEFAULT 0,
    is_anonymous_proxy INT(2) NOT NULL DEFAULT 0,
    is_satellite_provider INT(2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX start_ip_idx ON core_geoip_city_network_mapping (start_ip) USING BTREE;
CREATE INDEX mask_length_idx ON core_geoip_city_network_mapping (mask_length) USING BTREE;
CREATE INDEX city_id_idx ON core_geoip_city_network_mapping (city_id) USING BTREE;
CREATE INDEX is_anonymous_proxy_idx ON core_geoip_city_network_mapping (is_anonymous_proxy) USING BTREE;
CREATE INDEX is_satellite_provider_idx ON core_geoip_city_network_mapping (is_satellite_provider) USING BTREE;

