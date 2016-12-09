CREATE DATABASE rocket;

USE rocket;
CREATE TABLE rooms (
  id INTEGER NOT NULL PRIMARY KEY auto_increment,
  `name` VARCHAR(16) NOT NULL,
  `key` VARCHAR(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO rooms (`name`, `key`) VALUES ('Single Room', 'single');
INSERT INTO rooms (`name`, `key`) VALUES ('Double Room', 'double');


CREATE TABLE calendar_rooms (
  id INTEGER NOT NULL PRIMARY KEY auto_increment,
  room_id INTEGER NOT NULL,
  date_at DATE NOT NULL,
  price DECIMAL (11,2) NOT NULL DEFAULT 0,
  inventory INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

GRANT ALL ON rocket.* TO 'sqluser'@'%';
FLUSH PRIVILEGES;
