CREATE TABLE oauth_authorization_codes (
    authorization_code VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    redirect_uri VARCHAR(2000), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000), 
    CONSTRAINT auth_code_pk 
    PRIMARY KEY (authorization_code)
);