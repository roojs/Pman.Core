CREATE  TABLE core_notify_server (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    hostname VARCHAR(128) NOT NULL DEFAULT '',
    helo VARCHAR(128) NOT NULL DEFAULT '',
    poolname VARCHAR(128) NOT NULL DEFAULT '',
    is_active INT(4) NOT NULL DEFAULT 0,
    last_send DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
    
    PRIMARY KEY (id)
) ENGINE=InnoDB;;

ALTER TABLE core_notify_server ADD COLUMN ipv6_range_from VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_notify_server ADD COLUMN ipv6_range_to VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_notify_server ADD COLUMN ipv6_ptr VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE core_notify_server ADD COLUMN ipv6_sender_id INT NOT NULL DEFAULT 0;

ALTER TABLE core_notify_server ADD INDEX lookup (hostname,poolname,is_active);

-- Migration: Change ipv6_range_from and ipv6_range_to to VARBINARY(16)
-- First add temporary columns for the binary values
ALTER TABLE core_notify_server ADD COLUMN ipv6_range_from_bin VARBINARY(16) NOT NULL DEFAULT 0x0;
ALTER TABLE core_notify_server ADD COLUMN ipv6_range_to_bin VARBINARY(16) NOT NULL DEFAULT 0x0;

-- Convert existing VARCHAR data to binary (using inet6_aton for MySQL)
UPDATE core_notify_server 
SET ipv6_range_from_bin = INET6_ATON(ipv6_range_from) 
WHERE ipv6_range_from != '' AND ipv6_range_from IS NOT NULL;

UPDATE core_notify_server 
SET ipv6_range_to_bin = INET6_ATON(ipv6_range_to) 
WHERE ipv6_range_to != '' AND ipv6_range_to IS NOT NULL;

-- Drop old VARCHAR columns
ALTER TABLE core_notify_server DROP COLUMN ipv6_range_from;
ALTER TABLE core_notify_server DROP COLUMN ipv6_range_to;

-- Rename binary columns to original names
ALTER TABLE core_notify_server CHANGE COLUMN ipv6_range_from_bin ipv6_range_from VARBINARY(16) NOT NULL DEFAULT 0x0;
ALTER TABLE core_notify_server CHANGE COLUMN ipv6_range_to_bin ipv6_range_to VARBINARY(16) NOT NULL DEFAULT 0x0;
