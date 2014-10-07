CREATE OR REPLACE FUNCTION schema_create_table_statement(p_table_name varchar)
  RETURNS text AS
$BODY$
DECLARE
    v_table_ddl   text;
    column_record record;
    v_schema text;
BEGIN
    FOR column_record IN 
        SELECT 
            b.nspname as schema_name,
            b.relname as table_name,
            a.attname as column_name,
            pg_catalog.format_type(a.atttypid, a.atttypmod) as column_type,
            CASE WHEN 
                (SELECT substring(pg_catalog.pg_get_expr(d.adbin, d.adrelid) for 128)
                 FROM pg_catalog.pg_attrdef d
                 WHERE d.adrelid = a.attrelid AND d.adnum = a.attnum AND a.atthasdef) IS NOT NULL THEN
                'DEFAULT '|| (SELECT substring(pg_catalog.pg_get_expr(d.adbin, d.adrelid) for 128)
                              FROM pg_catalog.pg_attrdef d
                              WHERE d.adrelid = a.attrelid AND d.adnum = a.attnum AND a.atthasdef)
            ELSE
                ''
            END as column_default_value,
            CASE WHEN a.attnotnull = true THEN 
                'NOT NULL'
            ELSE
                'NULL'
            END as column_not_null,
            a.attnum as attnum,
            e.max_attnum as max_attnum
        FROM 
            pg_catalog.pg_attribute a
            INNER JOIN 
             (SELECT c.oid,
                n.nspname,
                c.relname
              FROM pg_catalog.pg_class c
                   LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
              WHERE c.relname ~ ('^('||p_table_name||')$')
                AND pg_catalog.pg_table_is_visible(c.oid)
              ORDER BY 2, 3) b
            ON a.attrelid = b.oid
            INNER JOIN 
             (SELECT 
                  a.attrelid,
                  max(a.attnum) as max_attnum
              FROM pg_catalog.pg_attribute a
              WHERE a.attnum > 0 
                AND NOT a.attisdropped
              GROUP BY a.attrelid) e
            ON a.attrelid=e.attrelid
        WHERE a.attnum > 0 
          AND NOT a.attisdropped
        ORDER BY a.attnum
    LOOP
        IF column_record.attnum = 1 THEN
            v_table_ddl:='CREATE TABLE '||column_record.schema_name||'.'||column_record.table_name||' ();'||chr(10)||chr(10);
        ELSE
            
        END IF;
        -- what does this do?
        v_schema := column_record.schema_name;
         
        IF column_record.attnum <= column_record.max_attnum THEN
            v_table_ddl:= v_table_ddl||chr(10)||
                     'ALTER TABLE  '||column_record.schema_name||'.'||column_record.table_name||
                     ' ADD COLUMN ' ||column_record.column_name||' '||column_record.column_type||' '||column_record.column_default_value||' '||column_record.column_not_null || ';';
        END IF;
    END LOOP;
    
    FOR column_record IN 
        SELECT
                c.oid,
                c.relname,
                a.attname,
                a.attnum,
                CASE
                    WHEN i.indisprimary THEN 'ALTER TABLE '||v_schema||'.'||p_table_name||' ADD CONSTRAINT ' || c.relname || ' PRIMARY KEY (' || a.attname || ');'
                     
                    WHEN i.indisunique THEN 'CREATE UNIQUE INDEX '|| c.relname || ' ON ' ||v_schema||'.'||p_table_name||' USING btree (' || a.attname || ');'
                    ELSE
                    'CREATE INDEX '|| c.relname || ' ON ' ||v_schema||'.'||p_table_name||' USING btree (' || ca.attname || ' );'
                END as iline
            FROM pg_index AS i, pg_class AS c, pg_attribute AS a
            WHERE i.indexrelid = c.oid AND i.indexrelid = a.attrelid AND i.indrelid = (v_schema|| '.' || p_table_name)::regclass

    LOOP
         
            v_table_ddl:= v_table_ddl||chr(10)||
                      column_record.iline;
         
    END LOOP;





    --v_table_ddl:=v_table_ddl||');';
    RETURN v_table_ddl;
END;
$BODY$
  LANGUAGE 'plpgsql' COST 100.0 SECURITY INVOKER;
  
SELECT schema_create_table_statement('item');
  