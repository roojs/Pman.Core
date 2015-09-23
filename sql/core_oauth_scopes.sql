CREATE TABLE core_oauth_scopes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    scope TEXT, 
    is_default BOOLEAN,
    PRIMARY KEY (id)
);

call mysql_change_engine('core_oauth_scopes');