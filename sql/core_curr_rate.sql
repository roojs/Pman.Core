CREATE  TABLE core_curr_rate (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    curr VARCHAR(255) NOT NULL DEFAULT '',
    rate FLOAT(11) NOT NULL DEFAULT 0,
    from_dt DATETIME  NOT NULL DEFAULT '1900-01-01 00:00:00',
    to_dt DATETIME  NOT NULL DEFAULT '1900-01-01 00:00:00',
    PRIMARY KEY (id)
);

ALTER TABLE core_curr_rate ADD INDEX core_curr_rate_curr_lookup (curr);
ALTER TABLE core_curr_rate ADD INDEX core_curr_rate_from_to_lookup (from_dt, to_dt);
ALTER TABLE core_curr_rate ADD INDEX core_curr_rate_lookup (curr, from_dt, to_dt);
