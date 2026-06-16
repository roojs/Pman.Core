CREATE  TABLE core_notify_blacklist (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    server_id INT(11) NOT NULL DEFAULT 0,
    domain_id INT(11) NOT NULL DEFAULT 0,
    added_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
    error_str TEXT NOT NULL DEFAULT '',
    
    PRIMARY KEY (id)
) ENGINE=InnoDB;;

ALTER TABLE core_notify_blacklist ADD COLUMN ip VARBINARY(16) NOT NULL DEFAULT 0x0;

ALTER TABLE core_notify_blacklist ADD INDEX lookup (server_id,domain_id);
