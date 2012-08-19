-- 
-- Creates the messages table for Zippy Chat
-- 
CREATE TABLE messages (
    messageid           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    roomid              INT(11) UNSIGNED NOT NULL,
    sentBySessionid     INT(11) UNSIGNED NOT NULL,
    username            VARCHAR(30) NOT NULL,
    dateSent            INT(11) UNSIGNED NOT NULL,
    message             MEDIUMTEXT,
    isCommand           TINYINT(1) NOT NULL,
    PRIMARY KEY (messageid)
) ENGINE=InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

-- 
-- Add an index for message lookups on pings
-- 
ALTER TABLE messages ADD INDEX messages_lookup (roomid, messageid) USING BTREE;  
