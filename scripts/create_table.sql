CREATE TABLE `spam_words` (
  `word` varchar(30) NOT NULL,
  `lastHitTime` int(11) unsigned NOT NULL,
  KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `spam_emails` (
  `email` varchar(30) NOT NULL COMMENT 'blocked emails for new user registration',
  `banTime` int(11) unsigned NOT NULL COMMENT 'time when the email get banned',
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
