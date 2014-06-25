CREATE TABLE core_oauth_refresh_tokens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    refresh_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000),
    PRIMARY KEY (id)
);