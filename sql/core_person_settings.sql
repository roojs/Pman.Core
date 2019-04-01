
CREATE TABLE core_person_settings (
    id int(11) NOT NULL auto_increment,  
    PRIMARY KEY   (id)
);

ALTER TABLE core_person_settings ADD COLUMN person_id INT(11) NOT NULL DEFAULT 0;
