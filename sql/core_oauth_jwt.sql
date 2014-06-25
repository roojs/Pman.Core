CREATE TABLE core_oauth_jwt (
    id INT(11) NOT NULL AUTO_INCREMENT,
    client_id VARCHAR(80) NOT NULL, 
    subject VARCHAR(80), 
    public_key VARCHAR(2000),
    PRIMARY KEY (id)
);

CREATE INDEX client_id_idx ON core_oauth_jwt (client_id) USING BTREE;
CREATE INDEX public_key_idx ON core_oauth_jwt (public_key) USING BTREE;