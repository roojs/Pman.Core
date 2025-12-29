CREATE TABLE core_notify_server_ipv6 (
    id INT NOT NULL AUTO_INCREMENT,
    server_id INT NOT NULL DEFAULT 0,
    ipv6_addr VARCHAR(255) NOT NULL DEFAULT '',
    domain_id INT NOT NULL DEFAULT 0,
    
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE core_notify_server_ipv6 DROP COLUMN server_id;

ALTER TABLE core_notify_server_ipv6 ADD INDEX lookup_addr (ipv6_addr);
ALTER TABLE core_notify_server_ipv6 ADD INDEX lookup_domain (domain_id);

ALTER TABLE core_notify_server_ipv6 ADD COLUMN allocation_reason TEXT NOT NULL DEFAULT '';

ALTER TABLE core_notify_server_ipv6 ADD COLUMN seq INT NOT NULL DEFAULT 0;
ALTER TABLE core_notify_server_ipv6 ADD COLUMN has_reverse_ptr INT NOT NULL DEFAULT 0;

ALTER TABLE core_notify_server_ipv6 ADD UNIQUE INDEX domain_seq (domain_id, seq);
ALTER TABLE core_notify_server_ipv6 ADD UNIQUE INDEX ipv6_seq (ipv6_addr, seq);
ALTER TABLE core_notify_server_ipv6 ADD UNIQUE INDEX domain_ipv6 (domain_id, ipv6_addr);
