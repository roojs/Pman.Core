
CREATE TABLE core_ip_access (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_ip_access ADD COLUMN ip VARCHAR(32) NOT NULL DEFAULT '';
ALTER TABLE core_ip_access ADD COLUMN created_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE core_ip_access CHANGE COLUMN created_dt created_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE core_ip_access ADD COLUMN status INT(2) NOT NULL DEFAULT 0; -- (-1 blocked, 0 new, 1 approved)
ALTER TABLE core_ip_access ADD COLUMN authorized_by INT(11) NOT NULL DEFAULT 0;
ALTER TABLE core_ip_access ADD COLUMN authorized_key VARCHAR(254) NOT NULL DEFAULT '';
ALTER TABLE core_ip_access ADD COLUMN email VARCHAR(254) NOT NULL DEFAULT '';
ALTER TABLE core_ip_access ADD COLUMN expire_dt DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE core_ip_access CHANGE COLUMN expire_dt expire_dt DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE core_ip_access ADD COLUMN user_agent VARCHAR(254) NOT NULL DEFAULT '';
ALTER TABLE core_ip_access ADD COLUMN updated_by INT(11) NOT NULL DEFAULT 0;

ALTER TABLE core_ip_access ADD INDEX ip_status_lookup(ip, status);

ALTER TABLE core_ip_access ADD INDEX lookup_authorized_by (authorized_by);
ALTER TABLE core_ip_access ADD INDEX lookup_updated_by (updated_by);