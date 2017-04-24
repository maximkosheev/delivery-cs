-- phpMyAdmin SQL Dump
-- version 4.0.10.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 25, 2017 at 09:00 AM
-- Server version: 5.5.41
-- PHP Version: 5.4.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `delivery-cs.local`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auth_assignment`
--

CREATE TABLE IF NOT EXISTS `tbl_auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  KEY `tbl_auth_assignment_ibfk_2` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auth_item`
--

CREATE TABLE IF NOT EXISTS `tbl_auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auth_item_child`
--

CREATE TABLE IF NOT EXISTS `tbl_auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auth_rule`
--

CREATE TABLE IF NOT EXISTS `tbl_auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_deliveryman`
--

CREATE TABLE IF NOT EXISTS `tbl_deliveryman` (
  `user_id` int(11) NOT NULL,
  `fio` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `passport` text,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_manager`
--

CREATE TABLE IF NOT EXISTS `tbl_manager` (
  `user_id` int(11) NOT NULL,
  `fio` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `passport` text,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_package`
--

CREATE TABLE IF NOT EXISTS `tbl_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deliveryman_id` int(11) DEFAULT NULL,
  `manager_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `package_type` int(11) NOT NULL,
  `address_from` varchar(255) DEFAULT NULL,
  `address_to` varchar(255) DEFAULT NULL,
  `phone_from` varchar(20) DEFAULT NULL,
  `phone_to` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `more` text,
  `cost` FLOAT DEFAULT NULL,
  `purchase_price` FLOAT DEFAULT NULL,
  `selling_price` FLOAT DEFAULT NULL,
  `create_time` datetime NOT NULL,
  `open_time` datetime DEFAULT NULL,
  `close_time` datetime DEFAULT NULL,
  `deadline_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_deliveryman_a` (`deliveryman_id`),
  KEY `fk_manager_b` (`manager_id`),
  KEY `fk_package_type_c` (`package_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_package_type`
--

CREATE TABLE IF NOT EXISTS `tbl_package_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(55) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_package_type_assignment`
--

CREATE TABLE IF NOT EXISTS `tbl_user_package_type_assignment` (
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`type_id`),
  KEY `fk_package_type` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_package_type_assignment`
--

CREATE TABLE IF NOT EXISTS `tbl_finances_log` (
  `deliveryman_id` int(11) NOT NULL,
  `cash` float NULL,
  `time` datetime NOT NULL,
  `description` text,
  KEY `fk_deliveryman_d` (`deliveryman_id`)
)ENGINE=InnoDB DEFAULT CHARSET =utf8;


--
-- Table structure for table tbl_stock
--

CREATE TABLE IF NOT EXISTS `tbl_stock` (
  id VARCHAR(22) NOT NULL,
  brand VARCHAR(22) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  purchase_price FLOAT DEFAULT 0.0,
  owner VARCHAR(22) DEFAULT '',
  UNIQUE KEY `article` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_auth_assignment`
--
ALTER TABLE `tbl_auth_assignment`
  ADD CONSTRAINT `tbl_auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `tbl_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_auth_assignment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_auth_item`
--
ALTER TABLE `tbl_auth_item`
  ADD CONSTRAINT `tbl_auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `tbl_auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_auth_item_child`
--
ALTER TABLE `tbl_auth_item_child`
  ADD CONSTRAINT `tbl_auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `tbl_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `tbl_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_deliveryman`
--
ALTER TABLE `tbl_deliveryman`
  ADD CONSTRAINT `fk_User2` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_manager`
--
ALTER TABLE `tbl_manager`
  ADD CONSTRAINT `fk_User1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_package`
--
ALTER TABLE `tbl_package`
  ADD CONSTRAINT `fk_deliveryman_a` FOREIGN KEY (`deliveryman_id`) REFERENCES `tbl_user` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_manager_b` FOREIGN KEY (`manager_id`) REFERENCES `tbl_user` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_package_type_c` FOREIGN KEY (`package_type`) REFERENCES `tbl_package_type` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user_package_type_assignment`
--
ALTER TABLE `tbl_user_package_type_assignment`
  ADD CONSTRAINT `fk_package_type` FOREIGN KEY (`type_id`) REFERENCES `tbl_package_type` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_assignment` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_finances_log`
--
ALTER TABLE `tbl_finances_log`
    ADD CONSTRAINT `fk_deliveryman_d` FOREIGN KEY (`deliveryman_id`) REFERENCES `tbl_user` (`id`) ON UPDATE CASCADE


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
