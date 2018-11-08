-- BC name..

CREATE TABLE  core_group_member  (
    id int(11)  NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
);

ALTER TABLE core_group_member CHANGE COLUMN  group_id  group_id int(11) NOT NULL default 0;
ALTER TABLE core_group_member ADD COLUMN  group_id int(11) NOT NULL default 0;

ALTER TABLE core_group_member ADD COLUMN   user_id int(11) NOT NULL default 0;

ALTER TABLE core_group_member ADD INDEX lookup_user_id (user_id);

-- remove duplicates... (hopefully only one duplicate.. failes if > 2...)
delete from  core_group_member where id in (
        select mid FROM (
            select
                concat(group_id,'-',user_id) as uid,
                max(id) as mid,
                count(*) as n
            from
                core_group_member
            group by
                group_id,user_id
            having n > 1
        ) s
);


ALTER TABLE core_group_member ADD UNIQUE KEY  unique_group_user (group_id,user_id);
