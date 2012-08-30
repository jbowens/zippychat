-- 
-- Creates the table that stores username changes
-- 
CREATE TABLE username_changes (
    changeid        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    roomid          INT(11) UNSIGNED NOT NULL,
    chatSessionid   BIGINT UNSIGNED NOT NULL,
    newUsername     VARCHAR(60) NOT NULL,
    PRIMARY KEY (changeid)
) ENGINE=InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin; 
