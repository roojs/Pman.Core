DROP FUNCTION IF EXISTS find_smallest_unused_ipv6;

DELIMITER $$

CREATE FUNCTION find_smallest_unused_ipv6(p_server_id INT)
RETURNS VARBINARY(16)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_prefix VARBINARY(14);
    DECLARE v_start_suffix INT;
    DECLARE v_end_suffix INT;
    DECLARE v_found_suffix INT DEFAULT NULL;
    DECLARE v_result VARBINARY(16);
    
    -- Get range boundaries from server
    SELECT 
        SUBSTR(ipv6_range_from, 1, 14),
        CONV(HEX(SUBSTR(ipv6_range_from, 15, 2)), 16, 10) + 1,
        CONV(HEX(SUBSTR(ipv6_range_to, 15, 2)), 16, 10)
    INTO v_prefix, v_start_suffix, v_end_suffix
    FROM core_notify_server
    WHERE id = p_server_id
    AND ipv6_range_from != 0x0
    AND ipv6_range_to != 0x0;
    
    -- If no valid range found, return NULL
    IF v_prefix IS NULL THEN
        RETURN NULL;
    END IF;
    
    -- Find smallest unused suffix
    SELECT MIN(candidate) INTO v_found_suffix
    FROM (
        SELECT v_start_suffix AS candidate
        UNION ALL
        SELECT CONV(HEX(SUBSTR(ipv6_addr, 15, 2)), 16, 10) + 1
        FROM core_notify_server_ipv6
        WHERE SUBSTR(ipv6_addr, 1, 14) = v_prefix
        AND CONV(HEX(SUBSTR(ipv6_addr, 15, 2)), 16, 10) >= v_start_suffix
        AND CONV(HEX(SUBSTR(ipv6_addr, 15, 2)), 16, 10) < v_end_suffix
    ) AS candidates
    WHERE candidate >= v_start_suffix
    AND candidate <= v_end_suffix
    AND candidate NOT IN (
        SELECT CONV(HEX(SUBSTR(ipv6_addr, 15, 2)), 16, 10)
        FROM core_notify_server_ipv6
        WHERE SUBSTR(ipv6_addr, 1, 14) = v_prefix
    );
    
    -- Build and return the full IPv6 address
    IF v_found_suffix IS NOT NULL THEN
        RETURN CONCAT(v_prefix, UNHEX(LPAD(HEX(v_found_suffix), 4, '0')));
    ELSE
        RETURN NULL;
    END IF;
END $$

DELIMITER ;