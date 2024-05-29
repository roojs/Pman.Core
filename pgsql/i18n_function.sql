




CREATE OR REPLACE FUNCTION i18n_translate(text,text,text)
  RETURNS text AS
$BODY$
DECLARE
    in_ltype ALIAS FOR $1;
    in_lkey ALIAS FOR $2;
    in_inlang ALIAS FOR $3;
     
    ret  TEXT;
     
BEGIN
 
         ret  := '';
        SELECT lval INTO ret FROM i18n
            WHERE ltype=in_ltype AND lkey=in_lkey and inlang=in_inlang LIMIT 1;
        RETURN ret;
         
    
    RETURN ret;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
  
ALTER FUNCTION i18n_translate(text,text,text)
  OWNER TO admin;




 