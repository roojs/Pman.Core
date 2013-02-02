

-- also update the pgsql version of these!?


CREATE OR REPLACE FUNCTION core_enum_display_name(integer)
  RETURNS text AS
$BODY$
 
DECLARE
    in_id ALIAS FOR $1;
    
    ret  TEXT;
     
BEGIN
 
    SELECT display_name INTO ret FROM core_enum
            WHERE id=in_id LIMIT 1;
            
    RETURN ret;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
  
ALTER FUNCTION core_enum_display_name(integer)
  OWNER TO admin;
  
  
 



CREATE OR REPLACE FUNCTION core_enum_name(integer)
  RETURNS text AS
$BODY$
DECLARE
    in_id ALIAS FOR $1;
    ret  TEXT;
BEGIN
 
     SELECT name INTO ret FROM core_enum
            WHERE id=in_id LIMIT 1;
        RETURN ret;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION core_enum_name(integer)
  OWNER TO admin;
  





CREATE OR REPLACE FUNCTION core_enum_name_to_display_name(text,text)
  RETURNS text AS
$BODY$
DECLARE
    in_etype ALIAS FOR $1;
    in_name ALIAS FOR $2;
    ret  TEXT;
BEGIN
 
      SELECT display_name INTO ret FROM core_enum
            WHERE name=in_name AND etype=in_etype LIMIT 1;
        RETURN ret;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION core_enum_name_to_display_name(text,text)
  OWNER TO admin;
  




CREATE OR REPLACE FUNCTION core_enum_id_by_name(text,text)
  RETURNS INTEGER AS
$BODY$
DECLARE
    in_etype ALIAS FOR $1;
    in_name ALIAS FOR $2;
    ret  INTEGER;
BEGIN
 
      SELECT id INTO ret FROM core_enum
            WHERE name=in_name AND etype=in_etype LIMIT 1;
        RETURN ret;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION  core_enum_id_by_name(text,text)
  OWNER TO admin;
  
   

  