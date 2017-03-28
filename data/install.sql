/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS tbl_user(
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  active TINYINT(1) NOT NULL DEFAULT 0,
  username VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tbl_manager(
  user_id INT(11) PRIMARY KEY,
  fio VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  passport TEXT DEFAULT NULL,
  photo VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE tbl_manager
    ADD CONSTRAINT fk_User1 FOREIGN KEY(user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS tbl_deliveryman(
  user_id INT(11) PRIMARY KEY,
  fio VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  passport TEXT DEFAULT NULL,
  photo VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE tbl_deliveryman
  ADD CONSTRAINT fk_User2 FOREIGN KEY(user_id) REFERENCES tbl_user(id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS tbl_package_type (
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  type VARCHAR(55) UNIQUE NOT NULL,
  description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tbl_user_package_type_assignment(
  user_id INT(11) NOT NULL,
  type_id INT(11) NOT NULL,
  PRIMARY KEY(user_id, type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE tbl_user_package_type_assignment
    ADD CONSTRAINT fk_package_type FOREIGN KEY(type_id) REFERENCES tbl_package_type(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_user_assignment FOREIGN KEY(user_id) REFERENCES tbl_user(id) on DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE tbl_package (
  id              INT(11) PRIMARY KEY AUTO_INCREMENT,
  deliveryman_id  INT(11) NULL,
  manager_id      INT(11) NOT NULL,
  status          INT(11) NOT NULL,
  package_type    INT(11) NOT NULL,
  address_from    VARCHAR(255) NULL,
  address_to      VARCHAR(255) NULL,
  phone_from      VARCHAR(20) NULL,
  phone_to        VARCHAR(20) NULL,
  model           VARCHAR(50) NULL,
  delivery_type   VARCHAR(50) NULL,
  more            TEXT NULL,
  cost            VARCHAR(12) NULL,
  purchase_price  VARCHAR(12) NULL,
  selling_price   VARCHAR(12) NULL,
  create_time     DATETIME NOT NULL,
  open_time       DATETIME,
  close_time      DATETIME,
  deadline_time   DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE tbl_package
    ADD CONSTRAINT fk_deliveryman_a FOREIGN KEY(deliveryman_id) REFERENCES tbl_user(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_manager_b FOREIGN KEY(manager_id) REFERENCES tbl_user(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_package_type_c FOREIGN KEY(package_type) REFERENCES tbl_package_type(id) ON DELETE RESTRICT ON UPDATE CASCADE;
