ALTER TABLE  `nodes` ADD  `hash` BINARY( 16 ) NULL DEFAULT NULL AFTER  `body` ;

ALTER TABLE  `nodes` DROP  `titleHash` , DROP  `bodyHash` ;

ALTER TABLE  `nodes` ADD UNIQUE (
`hash`
);


ALTER TABLE  `comments` ADD  `hash` BINARY( 16 ) NULL DEFAULT NULL AFTER  `body` ;

ALTER TABLE  `comments` ADD UNIQUE (
`hash`
);