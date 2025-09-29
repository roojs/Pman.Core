CREATE TABLE core_notify_server_ipv6_range (
    id INT NOT NULL AUTO_INCREMENT,
    server_id INT NOT NULL DEFAULT 0,
    ipv6_range_from VARCHAR(255) NOT NULL DEFAULT '',
    ipv6_range_to VARCHAR(255) NOT NULL DEFAULT '',
    ipv6_ptr VARCHAR(255) NOT NULL DEFAULT '',
    
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE core_notify_server_ipv6_range ADD INDEX lookup_server (server_id);
ALTER TABLE core_notify_server_ipv6_range ADD INDEX lookup_range (ipv6_range_from, ipv6_range_to);
