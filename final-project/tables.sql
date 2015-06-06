CREATE TABLE  `palmaa-db`.`users290` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`session_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
`date_registered` DATETIME NOT NULL ,
UNIQUE (
`email`
)
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci




CREATE TABLE  `palmaa-db`.`beers290` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT( 11 ) NOT NULL ,
`Beer` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`Enjoy` TINYINT( 1 ) NOT NULL ,
UNIQUE (
`uid` ,
`Beer`
)
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci