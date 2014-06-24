CREATE TABLE core_oauth_jwt (
    client_id VARCHAR(80) NOT NULL, 
    subject VARCHAR(80), 
    public_key VARCHAR(2000), 
    CONSTRAINT client_id_pk 
    PRIMARY KEY (client_id)
);