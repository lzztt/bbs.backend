# reset
echo 'UPDATE users SET contribution = 0, reputation = 0;'

# node
echo '
WITH
node_count AS (
    SELECT nodes.id, FLOOR(COUNT(DISTINCT comments.uid) / 3) AS count
    FROM nodes JOIN comments
        ON nodes.id = comments.nid AND comments.uid != nodes.uid
    GROUP BY nodes.id
)
SELECT * FROM node_count WHERE count > 0;
' \
| mysql -D hbbs -NB \
| awk '{print "UPDATE nodes SET reputation = " $2 " WHERE id = " $1 ";"}'

# time +
NOW=`date +%s`
echo "
UPDATE users SET reputation = reputation + FLOOR(($NOW - create_time) / 31536000);
"

# report +
echo '
SELECT reporter_uid, COUNT(DISTINCT cid)
    FROM node_complaints
    WHERE status = 2
    GROUP BY reporter_uid;
' \
| mysql -D hbbs -NB \
| awk '{print "UPDATE users SET contribution = contribution + " $2 " WHERE id = " $1 ";"}'

# report -
echo '
SELECT uid, COUNT(DISTINCT cid) * 3
    FROM node_complaints
    WHERE status = 2
    GROUP BY uid;
' \
| mysql -D hbbs -NB \
| awk '{print "UPDATE users SET contribution = contribution - " $2 ", reputation = reputation - " $2 " WHERE id = " $1 ";"}'

exit

echo '
WITH user_rep AS (
    SELECT uid, SUM(reputation) AS count
    FROM nodes GROUP BY uid
)
SELECT *
    FROM user_rep
    WHERE count > 0;
' \
| mysql -D hbbs -NB \
| awk '{print "UPDATE users SET reputation = reputation + " $2 " WHERE id = " $1 ";"}'
