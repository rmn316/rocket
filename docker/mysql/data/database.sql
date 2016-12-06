CREATE DATABASE rocket;

USE rocket;
CREATE TABLE rooms (
  id INTEGER NOT NULL PRIMARY KEY auto_increment,
  `name` VARCHAR(16) NOT NULL,
  `key` VARCHAR(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO rooms (`name`, `key`) VALUES ('Single Room', 'single');
INSERT INTO rooms (`name`, `key`) VALUES ('Double Room', 'double');

CREATE TABLE calendar_price_rooms (
  id INTEGER NOT NULL PRIMARY KEY auto_increment,
  room_id INTEGER NOT NULL,
  start_at DATE NOT NULL,
  end_at DATE NOT NULL,
  rule VARCHAR(64),
  price DECIMAL(11,2) NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO calendar_price_rooms(room_id, start_at, end_at, rule, price, created_at) VALUES (1, '2016-01-01', '2017-01-01', 'FREQ=DAILY', 15, now());
INSERT INTO calendar_price_rooms(room_id, start_at, end_at, rule, price, created_at) VALUES (2, '2016-01-01', '2017-01-01', 'FREQ=DAILY', 15, now());

CREATE TABLE calendar_inventory_rooms (
  id INTEGER NOT NULL PRIMARY KEY auto_increment,
  room_id INTEGER NOT NULL,
  start_at DATE NOT NULL,
  end_at DATE NOT NULL,
  rule VARCHAR(64),
  inventory INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

INSERT INTO calendar_inventory_rooms(room_id, start_at, end_at, rule, inventory, created_at) VALUES (1, '2016-01-01', '2017-01-01', 'FREQ=DAILY', 5, now());
INSERT INTO calendar_inventory_rooms(room_id, start_at, end_at, rule, inventory, created_at) VALUES (2, '2016-01-01', '2017-01-01', 'FREQ=DAILY', 5, now());

GRANT ALL ON rocket.* TO 'sqluser'@'%';
FLUSH PRIVILEGES;
