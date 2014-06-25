CREATE TABLE core_oauth_jwt (
    id INT(11) NOT NULL AUTO_INCREMENT,
    client_id VARCHAR(80) NOT NULL, 
    subject VARCHAR(80), 
    public_key VARCHAR(2000),
    PRIMARY KEY (id)
);