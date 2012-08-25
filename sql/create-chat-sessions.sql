--
-- Creates the chat sessions table for Zippy Chat
--
CREATE TABLE chat_sessions (
    chatSessionid          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    roomid                 INT(11) UNSIGNED NOT NULL,
    username               VARCHAR(30) NOT NULL,
    lastPing               INT(11) UNSIGNED NOT NULL,
    loginTime              INT(11) UNSIGNED NOT NULL,
    assignedGuestId        INT(11) UNSIGNED NOT NULL,
    active                 TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (chatSessionId)
) ENGINE=InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

--
-- Create an index good for looking up users in a room
--
ALTER TABLE chat_sessions ADD INDEX room_lookup (roomid) USING BTREE;

