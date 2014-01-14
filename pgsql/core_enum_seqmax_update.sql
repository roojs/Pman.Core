
DROP FUNCTION IF EXISTS core_enum_seqmax_update;

CREATE OR REPLACE FUNCTION core_enum_seqmax_update(in_etype varchar(128))
  RETURNS INTEGER AS
$BODY$
 
DECLARE
    v_seqmax INTEGER;
     
BEGIN
 
    SELECT MAX(seqid) +1 INTO v_seqmax FROM core_enum WHERE
            etype = in_etype;
    UPDATE core_enum SET seqmax = v_seqmax WHERE etype = in_etype;
    RETURN v_seqmax;
 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
  
ALTER FUNCTION core_enum_seqmax_update(varchar)
  OWNER TO admin;