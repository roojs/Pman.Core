
DROP FUNCTION IF EXISTS core_enum_translation_display_name;

DELIMITER $$
CREATE FUNCTION core_enum_translation_display_name(
    in_id INT(11),
    in_lang VARCHAR (256)
)  
RETURNS TEXT DETERMINISTIC
    BEGIN

        DECLARE v_ret TEXT;
        DECLARE v_orginal TEXT;
        SET v_ret = '';
        SET v_orginal = '';

        SELECT display_name INTO v_orginal FROM core_enum WHERE id = in_id;
        
        SELECT 
                txt 
        INTO 
                v_ret 
        FROM 
                cms_templatestr 
        WHERE 
                active = 1 
            AND 
                lang = in_lang 
            AND 
                on_id = in_id 
            AND 
                on_table = 'core_enum' 
            AND 
                on_col = 'display_name' 
        LIMIT 1;

        IF (v_ret IS NULL OR v_ret = '') THEN
            RETURN v_orginal;
        END IF;

        RETURN v_ret;

    END $$

DELIMITER ;