


DROP FUNCTION IF EXISTS person_has_perm;

DELIMITER $$
CREATE FUNCTION person_has_perm(  
       in_person_id INT(11),
       in_perm_name VARCHAR(64),
       in_perm_level VARCHAR(1)
    )  RETURNS INT(11) DETERMINISTIC
    BEGIN
        
        DECLARE v_id INT(11);
        SET v_id = 0;
        SELECT
                group_members.id INTO v_id 
            FROM
                group_rights 
            LEFT JOIN
                group_members
            ON 
                group_rights.group_id = group_members.group_id  
            WHERE 
                group_rights.rightname= in_perm_name AND 
                accessmask LIKE CONCAT('%', in_perm_level, '%') AND user_id = in_person_id LIMIT 1;
        
        RETURN v_id;
    END $$
DELIMITER ;
