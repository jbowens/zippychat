--
-- Creates the rooms table for Zippy Chat
-- 
CREATE TABLE rooms (
    roomid          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    title           VARCHAR(150) NOT NULL,
    description     MEDIUMTEXT,
    dateCreated     INT(11) UNSIGNED NOT NULL,
    creatorIp       INT(11) UNSIGNED NOT NULL,
    password        CHAR(32),
    lastGuestNumber MEDIUMINT(9),
    PRIMARY KEY (roomid)
) ENGINE=InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
