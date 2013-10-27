update privmsgs set body = subject where body is NULL or body = '';
update privmsgs set topicMID = mid where topicMID is NULL;

ALTER TABLE `privmsgs` CHANGE `fromUID` `fromUID` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE `toUID` `toUID` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE `topicMID` `topicMID` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE `time` `time` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `body` `body` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `isNew` `isNew` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `isDeleted` `isDeleted` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'perTopicResult: 0(0,0):none, 1(0,1):toUIDDelete, 2(1,0):fromUIDDelete, 3(1,1):bothDelete; action: toDelete: isDelete+=1, fromDelete:isDelete+=2*1';

ALTER TABLE `tags` ADD `tmp_cid` INT( 10 ) UNSIGNED NOT NULL;

ALTER TABLE `nodes`  ADD `tid` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'tag id' AFTER `uid`,  ADD INDEX (`tid`); 
ALTER TABLE `nodes` CHANGE `cid` `tmp_cid` INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `nodes` CHANGE `isLocked` `tmp_isLocked` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `nodes` CHANGE `isSticky` `weight` TINYINT UNSIGNED NULL DEFAULT '0' COMMENT 'sort condition, 1 > 0 > NULL. NULL will be the last, so set defalt to be 0';

CREATE TABLE IF NOT EXISTS `node_yellow_pages` (
  `nid` int(10) unsigned NOT NULL,
  `address` varchar(180) DEFAULT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `fax` varchar(60) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `website` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 ALTER TABLE `comments` ADD `tid` INT( 10 ) NULL DEFAULT NULL COMMENT 'tag id' AFTER `uid`;
