DELIMITER //

CREATE DEFINER=`web`@`localhost` PROCEDURE `checkpm`(IN check_uid INT, IN count_limit INT)
BEGIN 
    SELECT
        (SELECT username FROM users WHERE uid = p.fromUID) AS `from`, 
        (SELECT username FROM users WHERE uid = p.toUID) AS `to`,
        FROM_UNIXTIME(time),
        p.body
    FROM 
        privmsgs AS p 
    WHERE
        fromUID = check_uid
        OR
        toUID =check_uid
    ORDER BY
        mid DESC
    LIMIT
        count_limit; 
END//

DELIMITER ;
