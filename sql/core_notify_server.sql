CREATE  TABLE core_notify_server (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    hostname VARCHAR(128) NOT NULL DEFAULT '',
    helo VARCHAR(128) NOT NULL DEFAULT '',
    poolname VARCHAR(128) NOT NULL DEFAULT '',
    is_active INT(4) NOT NULL DEFAULT 0,
    last_send DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
    
    PRIMARY KEY (id)
) ENGINE=InnoDB;;

ALTER TABLE core_notify_server ADD INDEX lookup (hostname,poolname,is_active);
ALTER TABLE core_notify_server ADD COLUMN ipv6_range_from VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_notify_server ADD COLUMN ipv6_range_to VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_notify_server ADD COLUMN ipv6_ptr VARCHAR(255) NOT NULL DEFAULT '';
