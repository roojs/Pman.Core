CREATE TABLE core_oauth_clients (
    id INT(11) NOT NULL AUTO_INCREMENT ,
    client_id VARCHAR(80) NOT NULL,
    client_secret VARCHAR(80) NOT NULL, 
    redirect_uri VARCHAR(2000) NOT NULL, 
    grant_types VARCHAR(80), 
    scope VARCHAR(100), 
    user_id VARCHAR(80),
    PRIMARY KEY (id)
);