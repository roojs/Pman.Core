CREATE TABLE core_oauth_access_tokens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    access_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000), 
    PRIMARY KEY (id)
);

CREATE INDEX access_token_idx ON core_oauth_access_tokens (access_token) USING BTREE;
CREATE INDEX client_id_idx ON core_oauth_access_tokens (client_id) USING BTREE;
CREATE INDEX user_id_idx ON core_oauth_access_tokens (user_id) USING BTREE;