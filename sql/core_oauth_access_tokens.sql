CREATE TABLE core_oauth_access_tokens (
    access_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000), 
    CONSTRAINT access_token_pk 
    PRIMARY KEY (access_token)
);