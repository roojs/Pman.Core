CREATE TABLE core_oauth_refresh_tokens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    refresh_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000),
    PRIMARY KEY (id)
);

CREATE INDEX refresh_token_idx ON core_oauth_refresh_tokens (refresh_token) USING BTREE;
CREATE INDEX client_id_idx ON core_oauth_refresh_tokens (client_id) USING BTREE;
CREATE INDEX user_id_idx ON core_oauth_refresh_tokens (user_id) USING BTREE;