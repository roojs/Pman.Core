CREATE TABLE core_notify_server_ipv6 (
    id INT(11) NOT NULL AUTO_INCREMENT,
    range_id INT(11) NOT NULL DEFAULT 0,
    ipv6_addr VARCHAR(255) NOT NULL DEFAULT '',
    domain_id INT(11) NOT NULL DEFAULT 0,
    
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE core_notify_server_ipv6 ADD INDEX lookup_range (range_id);
ALTER TABLE core_notify_server_ipv6 ADD INDEX lookup_addr (ipv6_addr);
ALTER TABLE core_notify_server_ipv6 ADD INDEX lookup_domain (domain_id);
