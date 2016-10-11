


DROP FUNCTION IF EXISTS core_person_has_perm;

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
                core_group_member.id INTO v_id 
            FROM
                core_group_right
            LEFT JOIN
                core_group_member
            ON 
                core_group_right.group_id = core_group_member.group_id  
            WHERE 
                core_group_right.rightname= in_perm_name AND 
                accessmask LIKE CONCAT('%', in_perm_level, '%') AND user_id = in_person_id LIMIT 1;
        
        RETURN v_id;
    END $$
DELIMITER ;
