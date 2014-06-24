CREATE TABLE oauth_clients (
    client_id VARCHAR(80) NOT NULL,
    client_secret VARCHAR(80) NOT NULL, 
    redirect_uri VARCHAR(2000) NOT NULL, 
    grant_types VARCHAR(80), 
    scope VARCHAR(100), 
    user_id VARCHAR(80), 
    CONSTRAINT client_id_pk 
    PRIMARY KEY (client_id)
);
