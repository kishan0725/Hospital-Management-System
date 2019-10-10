-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 22, 2019 at 09:38 AM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myhmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admintb`
--

CREATE TABLE `admintb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admintb`
--

INSERT INTO `admintb` (`username`, `password`) VALUES
('admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `appointmenttb`
--

CREATE TABLE `appointmenttb` (
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `doctor` varchar(30) NOT NULL,
  `docFees` int(5) NOT NULL,
  `appdate` date NOT NULL,
  `apptime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `appointmenttb`
--

INSERT INTO `appointmenttb` (`fname`, `lname`, `email`, `contact`, `doctor`, `docFees`, `appdate`, `apptime`) VALUES
('Praveen', 'Kumar', 'praveen@gmail.com', '9988445566', 'Dinesh', 700, '2019-09-21', '10:00:00'),
('aziz', 'rahman', 'aziz@gmail.com', '9988667755', 'Dinesh', 700, '2019-09-25', '05:00:00'),
('Ram', 'Kumar', 'ram@gmail.com', '7896543210', 'arun', 600, '2019-09-22', '10:00:00'),
('Thiru', 'Selvam', 'thiru@gmail.com', '8899776655', 'Amit', 1000, '2019-09-30', '10:00:00'),
('Manoj', 'Kumar', 'manoj@gmail.com', '7799886633', 'ashok', 500, '2019-09-26', '05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `name` varchar(30) NOT NULL,
  `email` text NOT NULL,
  `contact` varchar(10) NOT NULL,
  `message` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`name`, `email`, `contact`, `message`) VALUES
('Anu', 'anu@gmail.com', '7896677554', 'Hey Admin'),
('Pradaap', 'pradaap@gmail.com', '9878765467', 'Nice Job'),
('Pradaap', 'pradaap@gmail.com', '9878765467', 'Nice Job'),
('Guru', 'guru@gmail.com', '7897656578', 'Great'),
(' Viki', 'viki@gmail.com', '9899778865', 'Good Job, Pal'),
('Ananya', 'ananya@gmail.com', '9997888879', 'How can I reach you?'),
('Aakash', 'aakash@gmail.com', '8788979967', 'Love your site');

-- --------------------------------------------------------

--
-- Table structure for table `doctb`
--

CREATE TABLE `doctb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `docFees` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `doctb`
--

INSERT INTO `doctb` (`username`, `password`, `email`, `docFees`) VALUES
('ashok', 'ashok123', 'ashok@gmail.com', 500),
('arun', 'arun123', 'arun@gmail.com', 600),
('Dinesh', 'dinesh123', 'dinesh@gmail.com', 700),
('Ganesh', 'ganesh123', 'ganesh@gmail.com', 550),
('Kumar', 'kumar123', 'kumar@gmail.com', 800),
('Amit', 'amit123', 'amit@gmail.com', 1000);

-- --------------------------------------------------------

--
-- Table structure for table `patreg`
--

CREATE TABLE `patreg` (
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `password` varchar(30) NOT NULL,
  `cpassword` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `patreg`
--

INSERT INTO `patreg` (`fname`, `lname`, `email`, `contact`, `password`, `cpassword`) VALUES
('Ajay', 'Kumar', 'ajaykumar@gmail.com', '9988776655', 'ajay123', 'ajay123'),
('Kishan', 'Lal', 'kishansmart0@gmail.com', '9876543210', 'kishan123', 'kishan123'),
('aziz', 'rahman', 'aziz@gmail.com', '9988667755', 'aziz123', 'aziz123'),
('Praveen', 'Kumar', 'praveen@gmail.com', '9988445566', 'praveen123', 'praveen123'),
('Ram', 'Kumar', 'ram@gmail.com', '7896543210', 'ram123', 'ram123'),
('Thiru', 'Selvam', 'thiru@gmail.com', '8899776655', 'thiru123', 'thiru123'),
('Manoj', 'Kumar', 'manoj@gmail.com', '7799886633', 'manoj123', 'manoj123');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
