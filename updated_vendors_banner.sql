-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 31, 2016 at 11:11 AM
-- Server version: 5.5.50-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tourpage`
--

-- --------------------------------------------------------

--
-- Table structure for table `vendors_banner`
--

CREATE TABLE IF NOT EXISTS `vendors_banner` (
  `bannerId` int(11) NOT NULL AUTO_INCREMENT,
  `vendorId` int(11) NOT NULL,
  `bannerImage` varchar(256) NOT NULL,
  `bannerLink` varchar(256) NOT NULL,
  `bannerStatus` tinyint(1) DEFAULT '1',
  `bannerCaption` varchar(256) DEFAULT NULL,
  `imageUploadedOn` date DEFAULT NULL,
  PRIMARY KEY (`bannerId`),
  KEY `vendorId` (`vendorId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `vendors_banner`
--

INSERT INTO `vendors_banner` (`bannerId`, `vendorId`, `bannerImage`, `bannerLink`, `bannerStatus`, `bannerCaption`, `imageUploadedOn`) VALUES
(7, 14, 'banner14394496951.jpg', 'www.example.org.ph', 1, 'This is a sample Caption 111', NULL),
(8, 14, 'banner14394496952.jpg', 'https://www.google.com', 1, 'This is a sample caption 222', NULL),
(9, 20, 'banner14461995781.jpg', 'facebook.com', 1, '', '0000-00-00'),
(10, 59, 'banner14480296561.jpg', 'https://www.facebook.com/?_rdr=p', 1, '', '0000-00-00'),
(12, 61, 'banner14500227321.jpg', 'http://www.grove86.com', 1, '', '0000-00-00'),
(13, 61, 'banner14500227911.jpg', 'http://www.grove86.com', 1, '', '0000-00-00'),
(15, 14, 'banner14726130571.png', 'https://www.google.com', 1, 'This is a restarant caption this is', '2016-08-31');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vendors_banner`
--
ALTER TABLE `vendors_banner`
  ADD CONSTRAINT `vendors_banner_ibfk_1` FOREIGN KEY (`vendorId`) REFERENCES `vendors` (`vendorId`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
