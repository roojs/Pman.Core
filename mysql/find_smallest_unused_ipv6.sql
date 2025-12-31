DELIMITER $$

CREATE PROCEDURE FindSmallestUnusedIpv6(
    IN p_server_id INT,
    OUT p_unused_ipv6 VARBINARY(16)
)
BEGIN
    DECLARE v_prefix VARBINARY(14);
    DECLARE v_start_suffix INT;
    DECLARE v_end_suffix INT;
    DECLARE v_found_suffix INT DEFAULT NULL;
    
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
        SET p_unused_ipv6 = NULL;
    ELSE
        -- Find smallest unused suffix
        -- Strategy: Check start_suffix first, then find gaps after used addresses
        SELECT MIN(candidate) INTO v_found_suffix
        FROM (
            -- Candidate: start_suffix (range_from + 1)
            SELECT v_start_suffix AS candidate
            UNION ALL
            -- Candidates: each used suffix + 1
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
        
        -- Build the full IPv6 address
        IF v_found_suffix IS NOT NULL THEN
            SET p_unused_ipv6 = CONCAT(
                v_prefix,
                UNHEX(LPAD(HEX(v_found_suffix), 4, '0'))
            );
        ELSE
            SET p_unused_ipv6 = NULL;
        END IF;
    END IF;
END $$

DELIMITER ;