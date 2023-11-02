CREATE  TABLE core_notify_sender_blacklist (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    domain_id INT(11) NOT NULL DEFAULT 0,
    sender_id INT(11) NOT NULL DEFAULT 0,
    added_dt DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
    error_str TEXT NOT NULL DEFAULT '',
    
    PRIMARY KEY (id)
) ENGINE=InnoDB; 

ALTER TABLE core_notify_sender_blacklist ADD INDEX lookup (sender_id,domain_id);
