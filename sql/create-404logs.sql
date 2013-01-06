--
-- Creates the 404 logs table for Zippy Chat
-- 
CREATE TABLE 404logs (
    path            VARCHAR(150) NOT NULL,
    whenAccessed    TIMESTAMP,
    ip              INT(11) NULL
) ENGINE=InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
