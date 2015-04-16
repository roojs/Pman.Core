

CREATE PROCEDURE core_enum_trigger_check (i_etype VARCHAR(128) , i_id INT)
 BEGIN
    DECLARE v_cnt INT;
    SET v_cnt = 0;
    SELECT count(*) INTO v_cnt WHERE etype = i_etype, id = i_id;
    IF (v_cnt < 1) THEN
         UPDATE `Core Enum Does not exist` SET x = 1;
 END;

