CREATE TABLE core_oauth_authorization_codes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    authorization_code VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    redirect_uri VARCHAR(2000), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000),
    PRIMARY KEY (id)
);

CREATE INDEX authorization_code_idx ON core_oauth_authorization_codes (authorization_code) USING BTREE;
CREATE INDEX client_id_idx ON core_oauth_authorization_codes (client_id) USING BTREE;
CREATE INDEX user_id_idx ON core_oauth_authorization_codes (user_id) USING BTREE;
CREATE INDEX redirect_uri_idx ON core_oauth_authorization_codes (redirect_uri) USING BTREE;