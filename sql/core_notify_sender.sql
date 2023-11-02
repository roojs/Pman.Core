CREATE  TABLE core_notify_sender (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    email VARCHAR(254) NOT NULL DEFAULT '',
    is_active INT(4) NOT NULL DEFAULT 0,
    poolname VARCHAR(128) NOT NULL DEFAULT '',
    priority INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;;

ALTER TABLE core_notify_sender ADD INDEX lookup (email,poolname,is_active);
