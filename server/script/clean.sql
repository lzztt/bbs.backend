--
-- Dumping routines for database 'houstonbbs'
--
/*!50003 DROP PROCEDURE IF EXISTS `clean` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50020 DEFINER=`web`@`localhost`*/ /*!50003 PROCEDURE `clean`()
    MODIFIES SQL DATA
    COMMENT 'clean houstonbbs database tables'
BEGIN
DROP TEMPORARY TABLE IF EXISTS userTmp, nodeTmp, commentTmp, nodePointTmp, commentPointTmp;
CREATE TEMPORARY TABLE userTmp AS
  SELECT uid FROM users WHERE status = 0;
ALTER TABLE userTmp ADD PRIMARY KEY (uid);

CREATE TEMPORARY TABLE nodeTmp AS
  SELECT nid FROM nodes WHERE status = 0 OR uid IN (SELECT uid FROM userTmp);
ALTER TABLE nodeTmp ADD PRIMARY KEY (nid);

CREATE TEMPORARY TABLE commentTmp AS
  SELECT cid FROM comments WHERE nid IN (SELECT nid FROM nodeTmp) OR uid IN (SELECT uid FROM userTmp);
ALTER TABLE commentTmp ADD PRIMARY KEY (cid);

CREATE TEMPORARY TABLE nodePointTmp AS
  SELECT uid, COUNT(*)*3 AS point FROM nodes WHERE nid IN (SELECT nid FROM nodeTmp) AND uid NOT IN (SELECT uid FROM userTmp) GROUP BY uid;
ALTER TABLE nodePointTmp ADD PRIMARY KEY (uid);

CREATE TEMPORARY TABLE commentPointTmp AS
  SELECT uid, COUNT(*) AS point FROM comments WHERE nid IN (SELECT nid FROM nodeTmp) AND uid NOT IN (SELECT uid FROM userTmp) GROUP BY uid;
ALTER TABLE commentPointTmp ADD PRIMARY KEY (uid);

DELETE FROM users WHERE uid IN (SELECT uid FROM userTmp);
DELETE FROM nodes WHERE nid IN (SELECT nid FROM nodeTmp);
DELETE FROM comments WHERE cid IN (SELECT cid FROM commentTmp);
DELETE FROM pm USING privmsgs AS pm JOIN userTmp AS u ON pm.fromUID = u.uid OR pm.toUID = u.uid;
DELETE FROM Session WHERE uid IN (SELECT uid FROM userTmp);
DELETE FROM yp_rating WHERE uid IN (SELECT uid FROM userTmp);

INSERT INTO files_deleted (fid, path) SELECT fid, path FROM files WHERE nid IN (SELECT nid FROM nodeTmp) OR cid IN (SELECT cid FROM commentTmp);
DELETE FROM files WHERE nid IN (SELECT nid FROM nodeTmp) OR cid IN (SELECT cid FROM commentTmp);
DELETE FROM Activity WHERE nid IN (SELECT nid FROM nodeTmp);
DELETE FROM yp_rating WHERE nid IN (SELECT nid FROM nodeTmp);
UPDATE users AS u JOIN nodePointTmp AS p ON u.uid = p.uid SET u.points = IF(u.points > p.point, u.points - p.point, 0);
UPDATE users AS u JOIN commentPointTmp AS p ON u.uid = p.uid SET u.points = IF(u.points > p.point, u.points - p.point, 0);

DROP TEMPORARY TABLE userTmp, nodeTmp, commentTmp, nodePointTmp, commentPointTmp;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
