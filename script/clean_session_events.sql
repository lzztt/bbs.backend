SELECT count(*)
FROM session_events AS se
    JOIN (
        SELECT user_id, hash, ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY time DESC) AS rn
        FROM (
            SELECT user_id, hash, max(time) AS time
            FROM session_events
            GROUP BY user_id, hash
            ) AS t
        ) AS t1
    ON se.user_id = t1.user_id AND se.hash = t1.hash
WHERE t1.rn > 3;

DELETE se.*
FROM session_events AS se
    JOIN (
        SELECT user_id, hash, ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY time DESC) AS rn
        FROM (
            SELECT user_id, hash, MAX(time) AS time
            FROM session_events
            GROUP BY user_id, hash
            ) AS t
        ) AS t1
    ON se.user_id = t1.user_id AND se.hash = t1.hash
WHERE t1.rn > 3;


SELECT count(*)
FROM (
    SELECT id, hash, ROW_NUMBER() OVER (PARTITION BY user_id, hash ORDER BY time DESC) AS rn
    FROM session_events
    ) AS t
WHERE t.rn > 3;

DELETE FROM session_events
WHERE id IN (
    SELECT id
    FROM (
        SELECT id, hash, ROW_NUMBER() OVER (PARTITION BY user_id, hash ORDER BY time DESC) AS rn
        FROM session_events) AS t
        WHERE t.rn > 3
    );

DELETE se.*
FROM session_events AS se
    JOIN (
        SELECT id
        FROM (
            SELECT id, hash, ROW_NUMBER() OVER (PARTITION BY user_id, hash ORDER BY time DESC) AS rn
            FROM session_events) AS t
        WHERE t.rn > 3
        ) AS t1
    ON se.id = t1.id;
