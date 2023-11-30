
DROP FUNCTION IF EXISTS core_translate_lookup;

DELIMITER $$
CREATE FUNCTION core_translate_lookup(
    in_ontable VARCHAR(128),
    in_onid INT(11),
    in_col VARCHAR(128),
    in_lang VARCHAR(8),
    in_default TEXT
)  
RETURNS TEXT NOT DETERMINISTIC READS SQL DATA 
    BEGIN

        DECLARE v_ret TEXT;
        DECLARE v_id INT(11);
        DECLARE v_src_id INT(11);
   		DECLARE s_id INT(11);

        IF LENGTH(in_default) < 1 THEN
            RETURN in_default;
        END IF;
        
        SET v_id = 0;
        
        SELECT
            id , txt, src_id
            INTO
            v_id, v_ret, v_src_id
        FROM
            core_templatestr
        WHERE
            on_id = in_onid
            AND
            on_table = in_ontable
            AND
            on_col = in_col
            AND
            lang = in_lang
            AND
            active = 1
        LIMIT 1;
            
        IF v_id < 1 OR LENGTH(v_ret) < 1 THEN
            RETURN in_default;
        END IF;

       	SELECT 
       		id
       		INTO
       		s_id
       	FROM
       		core_templatestr
   	   	WHERE
   	   		id = v_src_id
   			AND
   			mdsum = MD5(in_default);
		
		IF s_id IS NULL THEN
			RETURN in_default;
		END IF;
        
        RETURN v_ret;

    END $$

DELIMITER ;