-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 04, 2026 at 09:13 AM
-- Server version: 10.11.11-MariaDB-cll-lve
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chuduyit_medical_k73`
--

-- --------------------------------------------------------

--
-- Table structure for table `admintb`
--

CREATE TABLE `admintb` (
  `username` varchar(50) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admintb`
--

INSERT INTO `admintb` (`username`, `password`) VALUES
('admin', '123');

-- --------------------------------------------------------

--
-- Table structure for table `appointmenttb`
--

CREATE TABLE `appointmenttb` (
  `ID` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(30) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `doctor` varchar(30) NOT NULL,
  `docFees` int(5) NOT NULL,
  `appdate` date NOT NULL,
  `apptime` time NOT NULL,
  `slot_id` int(11) DEFAULT NULL,
  `userStatus` int(5) NOT NULL,
  `doctorStatus` int(5) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointmenttb`
--

INSERT INTO `appointmenttb` (`ID`, `pid`, `fname`, `lname`, `gender`, `email`, `contact`, `doctor`, `docFees`, `appdate`, `apptime`, `slot_id`, `userStatus`, `doctorStatus`, `notes`, `created_at`) VALUES
(1, 1, 'An', 'Nguyễn Văn', 'Male', 'an.nguyen@email.com', '0912345678', 'Lê Minh Châu', 250000, '2026-02-14', '09:00:00', 1, 1, 1, 'Khám ho, sốt cho bé', '2026-01-28 09:00:00'),
(2, 2, 'Bình', 'Trần Thị', 'Female', 'binh.tran@email.com', '0987654321', 'Vũ Thị Giang', 300000, '2026-02-15', '10:30:00', 2, 0, 1, 'Đau bụng dưới', '2026-01-28 09:15:00'),
(3, 3, 'Cường', 'Lê Mạnh', 'Male', 'cuong.le@email.com', '0901234567', 'Hoàng Văn Em', 320000, '2026-02-16', '14:00:00', 3, 1, 0, 'Ngứa da, nổi mẩn đỏ', '2026-01-28 09:30:00'),
(4, 4, 'Duy', 'Chu Quang', 'Male', 'duywinter@gmail.com', '0846181174', 'Bùi Quốc Việt', 400000, '2026-03-18', '13:30:00', 48, 1, 1, 'Tái khám xương khớp', '2026-01-15 14:11:50'),
(5, 5, 'Hạnh', 'Phạm Thị', 'Female', 'hanh.pham@email.com', '0918273645', 'Lý Văn Minh', 320000, '2026-02-17', '08:30:00', 5, 1, 1, 'Đau dạ dày', '2026-01-28 10:00:00'),
(6, 6, 'Lan', 'Hoàng Ngọc', 'Female', 'lan.hoang@email.com', '0922334455', 'Nguyễn Văn An', 300000, '2026-02-18', '15:00:00', 6, 0, 1, 'Khó thở, tim đập nhanh', '2026-01-28 10:30:00'),
(7, 7, 'Minh', 'Vũ Đức', 'Male', 'minh.vu@email.com', '0933445566', 'Phan Văn Sơn', 350000, '2026-02-19', '11:00:00', 7, 1, 1, 'Đau lưng lan xuống chân', '2026-01-28 11:00:00'),
(8, 8, 'Nhi', 'Đặng Uyên', 'Female', 'nhi.dang@email.com', '0944556677', 'Võ Thị Yến', 280000, '2026-02-20', '09:30:00', 8, 0, 1, 'Niềng răng tư vấn', '2026-01-28 11:30:00'),
(9, 9, 'Quân', 'Trịnh Minh', 'Male', 'quan.trinh@email.com', '0955667788', 'Bùi Văn Kiên', 400000, '2026-02-21', '16:00:00', 9, 1, 0, 'Đau đầu thường xuyên', '2026-01-28 12:00:00'),
(10, 10, 'Tâm', 'Lý Thanh', 'Female', 'tam.ly@email.com', '0966778899', 'Trần Thị Hương', 220000, '2026-02-22', '08:00:00', 10, 1, 1, 'Khám tổng quát', '2026-01-28 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `name` varchar(30) NOT NULL,
  `email` text NOT NULL,
  `contact` varchar(10) NOT NULL,
  `message` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`name`, `email`, `contact`, `message`) VALUES
('Nguyễn Văn An', 'an.nguyen@gmail.com', '0912345678', 'Hệ thống đặt lịch rất tiện lợi, cảm ơn đội ngũ phát triển.'),
('Trần Thị Bình', 'binh.tran@gmail.com', '0987654321', 'Tôi gặp chút khó khăn khi tìm bác sĩ theo chuyên khoa, cần cải thiện bộ lọc.'),
('Lê Minh', 'minh.le@hotmail.com', '0909090909', 'Dịch vụ tốt, bác sĩ tận tâm. Ủng hộ 5 sao!'),
('Phạm Quang Huy', 'huy.pham@outlook.com', '0918273645', 'Giao diện web rất đẹp và dễ sử dụng.'),
('Hoàng Thùy Linh', 'linh.hoang@gmail.com', '0922334455', 'Cần thêm tính năng nhắc lịch qua SMS thì tuyệt vời hơn.'),
('Duy Chu Quang', 'duywinter@gmail.com', '0846181174', 'Hệ thống hoạt động ổn định, các chức năng mới cập nhật rất hữu ích.');

-- --------------------------------------------------------

--
-- Table structure for table `doctb`
--

CREATE TABLE `doctb` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `spec` varchar(255) NOT NULL,
  `spec_id` int(11) DEFAULT NULL,
  `docFees` int(10) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` mediumtext DEFAULT NULL,
  `experience_years` int(3) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `average_rating` decimal(3,2) DEFAULT NULL COMMENT 'Average rating 1.00-5.00',
  `total_ratings` int(11) DEFAULT 0 COMMENT 'Total number of ratings'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctb`
--

INSERT INTO `doctb` (`id`, `username`, `password`, `fullname`, `email`, `spec`, `spec_id`, `docFees`, `phone`, `avatar`, `bio`, `experience_years`, `status`, `created_at`, `average_rating`, `total_ratings`) VALUES
(1, 'le.chau', '123', 'Lê Minh Châu', 'le.chau@hospital.vn', 'Pediatrics', 1, 250000, '0901234569', NULL, 'Bác sĩ nhi khoa tận tâm, yêu trẻ em', 10, 1, '2026-01-14 18:04:43', NULL, 0),
(2, 'pham.dung', '123', 'Phạm Thị Dung', 'pham.dung@hospital.vn', 'Pediatrics', 1, 280000, '0901234570', NULL, 'Chuyên gia nhi khoa, đặc biệt về bệnh hô hấp trẻ em', 8, 1, '2026-01-14 18:04:43', NULL, 0),
(3, 'vu.giang', '123', 'Vũ Thị Giang', 'vu.giang@hospital.vn', 'Obstetrics_Gynecology', 2, 300000, '0901234573', NULL, 'Chuyên gia sản phụ khoa, đỡ đẻ hơn 5000 ca', 16, 1, '2026-01-14 18:04:43', NULL, 0),
(4, 'dang.hung', '123', 'Đặng Văn Hùng', 'dang.hung@hospital.vn', 'Obstetrics_Gynecology', 2, 350000, '0901234574', NULL, 'Bác sĩ sản khoa, chuyên thai kỳ nguy cơ cao', 13, 1, '2026-01-14 18:04:43', NULL, 0),
(5, 'hoang.em', '123', 'Hoàng Văn Em', 'hoang.em@hospital.vn', 'Dermatology', 3, 320000, '0901234571', NULL, 'Chuyên gia da liễu, điều trị mụn và các bệnh da mãn tính', 14, 1, '2026-01-14 18:04:43', NULL, 0),
(6, 'ngo.phuong', '123', 'Ngô Thị Phương', 'ngo.phuong@hospital.vn', 'Dermatology', 3, 350000, '0901234572', NULL, 'Bác sĩ da liễu thẩm mỹ, chuyên trị nám và tàn nhang', 11, 1, '2026-01-14 18:04:43', NULL, 0),
(7, 'ly.minh', '123', 'Lý Văn Minh', 'ly.minh@hospital.vn', 'Gastroenterology', 4, 320000, '0901234577', NULL, 'Chuyên gia nội soi tiêu hóa, điều trị viêm loét dạ dày', 14, 1, '2026-01-14 18:04:43', NULL, 0),
(8, 'mai.ngoc', '123', 'Mai Thị Ngọc', 'mai.ngoc@hospital.vn', 'Gastroenterology', 4, 280000, '0901234578', NULL, 'Bác sĩ tiêu hóa, chuyên bệnh gan mật', 9, 1, '2026-01-14 18:04:43', NULL, 0),
(9, 'phan.son', '123', 'Phan Văn Sơn', 'phan.son@hospital.vn', 'Rheumatology', 5, 350000, '0901234581', NULL, 'Chuyên gia xương khớp, điều trị viêm khớp dạng thấp', 15, 1, '2026-01-14 18:04:43', NULL, 0),
(10, 'cao.tam', '123', 'Cao Thị Tâm', 'cao.tam@hospital.vn', 'Rheumatology', 5, 300000, '0901234582', NULL, 'Bác sĩ cơ xương khớp, chuyên gout và loãng xương', 10, 1, '2026-01-14 18:04:43', NULL, 0),
(11, 'dinh.phong', '123', 'Đinh Văn Phong', 'dinh.phong@hospital.vn', 'ENT', 8, 280000, '0901234579', NULL, 'Bác sĩ TMH, phẫu thuật nội soi xoang', 11, 1, '2026-01-14 18:04:43', NULL, 0),
(12, 'to.quynh', '123', 'Tô Thị Quỳnh', 'to.quynh@hospital.vn', 'ENT', 8, 260000, '0901234580', NULL, 'Bác sĩ TMH, điều trị viêm họng và viêm amidan', 8, 1, '2026-01-14 18:04:43', NULL, 0),
(13, 'le.duc', '123', 'Lê Văn Đức', 'le.duc@hospital.vn', 'Oncology', 9, 500000, '0901234589', NULL, 'Giáo sư ung bướu, chuyên gia hóa trị', 17, 1, '2026-01-14 18:04:43', NULL, 0),
(14, 'pham.mai', '123', 'Phạm Thị Mai', 'pham.mai@hospital.vn', 'Oncology', 9, 450000, '0901234590', NULL, 'Bác sĩ ung bướu, xạ trị và điều trị đích', 14, 1, '2026-01-14 18:04:43', NULL, 0),
(15, 'nguyen.an', '123', 'Nguyễn Văn An', 'nguyen.an@hospital.vn', 'Cardiology', 10, 300000, '0901234567', NULL, 'Chuyên gia tim mạch với 15 năm kinh nghiệm, từng tu nghiệp tại Pháp', 15, 1, '2026-01-14 18:04:43', NULL, 0),
(16, 'tran.binh', '123', 'Trần Thị Bình', 'tran.binh@hospital.vn', 'Cardiology', 10, 350000, '0901234568', NULL, 'Bác sĩ chuyên khoa II Tim mạch, giảng viên Đại học Y', 12, 1, '2026-01-14 18:04:43', NULL, 0),
(17, 'bui.viet', '123', 'Bùi Quốc Việt', 'bui.viet@hospital.vn', 'Orthopedics', 12, 400000, '0901234595', NULL, 'Phẫu thuật viên chỉnh hình, thay khớp háng và gối', 18, 1, '2026-01-14 18:04:43', NULL, 0),
(18, 'truong.nam', '123', 'Trương Văn Nam', 'truong.nam@hospital.vn', 'Orthopedics', 12, 350000, '0901234596', NULL, 'Bác sĩ chấn thương, nội soi khớp vai và gối', 12, 1, '2026-01-14 18:04:43', NULL, 0),
(19, 'lam.xuan', '123', 'Lâm Văn Xuân', 'lam.xuan@hospital.vn', 'Dentistry', 16, 250000, '0901234585', NULL, 'Bác sĩ RHM, chuyên nhổ răng khôn và implant', 12, 1, '2026-01-14 18:04:43', NULL, 0),
(20, 'vo.yen', '123', 'Võ Thị Yến', 'vo.yen@hospital.vn', 'Dentistry', 16, 280000, '0901234586', NULL, 'Bác sĩ nha khoa thẩm mỹ, bọc răng sứ', 8, 1, '2026-01-14 18:04:43', NULL, 0),
(21, 'vu.long', '123', 'Vũ Đình Long', 'vu.long@hospital.vn', 'Endocrinology', 19, 350000, '0901234593', NULL, 'Chuyên gia nội tiết, điều trị tiểu đường và tuyến giáp', 16, 1, '2026-01-14 18:04:43', NULL, 0),
(22, 'dang.linh', '123', 'Đặng Thị Linh', 'dang.linh@hospital.vn', 'Endocrinology', 19, 320000, '0901234594', NULL, 'Bác sĩ nội tiết, rối loạn chuyển hóa', 11, 1, '2026-01-14 18:04:43', NULL, 0),
(23, 'ly.hoang', '123', 'Lý Minh Hoàng', 'ly.hoang@hospital.vn', 'Psychiatry', 20, 400000, '0901234597', NULL, 'Bác sĩ tâm thần, điều trị trầm cảm và lo âu', 15, 1, '2026-01-14 18:04:43', NULL, 0),
(24, 'mai.nga', '123', 'Mai Thanh Nga', 'mai.nga@hospital.vn', 'Psychiatry', 20, 350000, '0901234598', NULL, 'Bác sĩ tâm thần, rối loạn giấc ngủ', 10, 1, '2026-01-14 18:04:43', NULL, 0),
(25, 'hoang.quan', '123', 'Hoàng Minh Quân', 'hoang.quan@hospital.vn', 'Pulmonology', 21, 300000, '0901234591', NULL, 'Bác sĩ hô hấp, điều trị hen suyễn và COPD', 13, 1, '2026-01-14 18:04:43', NULL, 0),
(26, 'ngo.thao', '123', 'Ngô Thị Thảo', 'ngo.thao@hospital.vn', 'Pulmonology', 21, 280000, '0901234592', NULL, 'Bác sĩ phổi, nội soi phế quản', 10, 1, '2026-01-14 18:04:43', NULL, 0),
(27, 'bui.kien', '123', 'Bùi Văn Kiên', 'bui.kien@hospital.vn', 'Neurology', 25, 400000, '0901234575', NULL, 'Giáo sư thần kinh học, chuyên gia đột quỵ', 18, 1, '2026-01-14 18:04:43', NULL, 0),
(28, 'truong.lan', '123', 'Trương Thị Lan', 'truong.lan@hospital.vn', 'Neurology', 25, 350000, '0901234576', NULL, 'Bác sĩ thần kinh, điều trị đau đầu và động kinh', 12, 1, '2026-01-14 18:04:43', NULL, 0),
(29, 'dinh.danh', '123', 'Đinh Công Danh', 'dinh.danh@hospital.vn', 'Traditional_Medicine', 29, 250000, '0901234599', NULL, 'Lương y, châm cứu và bấm huyệt', 22, 1, '2026-01-14 18:04:43', NULL, 0),
(30, 'to.hanh', '123', 'Tô Thị Hạnh', 'to.hanh@hospital.vn', 'Traditional_Medicine', 29, 230000, '0901234600', NULL, 'Bác sĩ YHCT, thuốc nam và thuốc bắc', 18, 1, '2026-01-14 18:04:43', NULL, 0),
(31, 'duong.uy', '123', 'Dương Văn Uy', 'duong.uy@hospital.vn', 'Ophthalmology', 32, 300000, '0901234583', NULL, 'Bác sĩ nhãn khoa, phẫu thuật đục thủy tinh thể', 13, 1, '2026-01-14 18:04:43', NULL, 0),
(32, 'ho.van', '123', 'Hồ Thị Vân', 'ho.van@hospital.vn', 'Ophthalmology', 32, 280000, '0901234584', NULL, 'Bác sĩ mắt, điều trị cận thị và tật khúc xạ', 9, 1, '2026-01-14 18:04:43', NULL, 0),
(33, 'nguyen.tung', '123', 'Nguyễn Thanh Tùng', 'nguyen.tung@hospital.vn', 'Internal_Medicine', 37, 200000, '0901234587', NULL, 'Bác sĩ nội khoa tổng quát, kinh nghiệm 20 năm', 20, 1, '2026-01-14 18:04:43', NULL, 0),
(34, 'tran.huong', '123', 'Trần Thị Hương', 'tran.huong@hospital.vn', 'Internal_Medicine', 37, 220000, '0901234588', NULL, 'Bác sĩ đa khoa, khám sức khỏe tổng quát', 15, 1, '2026-01-14 18:04:43', NULL, 0),
(35, 'le.hai', '123', 'Lê Văn Hải', 'le.hai@hospital.vn', 'Allergy_Immunology', 6, 320000, '0901234601', NULL, 'Chuyên gia dị ứng, điều trị hen phế quản và viêm mũi dị ứng', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(36, 'pham.thu', '123', 'Phạm Thị Thu', 'pham.thu@hospital.vn', 'Allergy_Immunology', 6, 300000, '0901234602', NULL, 'Bác sĩ dị ứng, test và điều trị dị ứng thực phẩm', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(37, 'vu.tuan', '123', 'Vũ Anh Tuấn', 'vu.tuan@hospital.vn', 'Anesthesiology', 7, 350000, '0901234603', NULL, 'Bác sĩ gây mê hồi sức, chuyên gây mê ngoài màng cứng', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(38, 'dang.hoa', '123', 'Đặng Thị Hoa', 'dang.hoa@hospital.vn', 'Anesthesiology', 7, 330000, '0901234604', NULL, 'Bác sĩ gây mê, kiểm soát đau sau phẫu thuật', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(39, 'hoang.khang', '123', 'Hoàng Minh Khang', 'hoang.khang@hospital.vn', 'Geriatrics', 11, 280000, '0901234605', NULL, 'Bác sĩ lão khoa, chăm sóc người cao tuổi mắc nhiều bệnh mãn tính', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(40, 'ngo.phuong', '123', 'Ngô Thị Phương Nga', 'ngo.phuong@hospital.vn', 'Geriatrics', 11, 260000, '0901234606', NULL, 'Bác sĩ lão khoa, phục hồi chức năng sau đột quỵ', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(41, 'ly.thanh', '123', 'Lý Quốc Thanh', 'ly.thanh@hospital.vn', 'Emergency_Medicine', 13, 400000, '0901234607', NULL, 'Bác sĩ cấp cứu, hồi sức tim phổi', 17, 1, '2026-01-14 11:04:43', NULL, 0),
(42, 'mai.lan', '123', 'Mai Thị Lan Anh', 'mai.lan@hospital.vn', 'Emergency_Medicine', 13, 380000, '0901234608', NULL, 'Bác sĩ cấp cứu, xử lý chấn thương đa khoa', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(43, 'phan.cuong', '123', 'Phan Văn Cường', 'phan.cuong@hospital.vn', 'General_Surgery', 14, 450000, '0901234609', NULL, 'Phẫu thuật viên tổng quát, mổ nội soi ổ bụng', 18, 1, '2026-01-14 11:04:43', NULL, 0),
(44, 'cao.huyen', '123', 'Cao Thị Huyền', 'cao.huyen@hospital.vn', 'General_Surgery', 14, 420000, '0901234610', NULL, 'Bác sĩ ngoại khoa, phẫu thuật tuyến giáp và vú', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(45, 'dinh.quang', '123', 'Đinh Quang Dũng', 'dinh.quang@hospital.vn', 'Preventive_Medicine', 15, 200000, '0901234611', NULL, 'Bác sĩ y học dự phòng, tư vấn dinh dưỡng và lối sống', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(46, 'to.my', '123', 'Tô Thị Mỹ Linh', 'to.my@hospital.vn', 'Preventive_Medicine', 15, 220000, '0901234612', NULL, 'Bác sĩ y học dự phòng, tiêm chủng và tư vấn sức khỏe', 8, 1, '2026-01-14 11:04:43', NULL, 0),
(47, 'lam.duy', '123', 'Lâm Văn Duy', 'lam.duy@hospital.vn', 'Infectious_Disease', 17, 350000, '0901234613', NULL, 'Bác sĩ truyền nhiễm, điều trị sốt xuất huyết và sốt rét', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(48, 'vo.chi', '123', 'Võ Thị Chi', 'vo.chi@hospital.vn', 'Infectious_Disease', 17, 330000, '0901234614', NULL, 'Bác sĩ truyền nhiễm, HIV/AIDS và viêm gan virus', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(49, 'vu.bao', '123', 'Vũ Quốc Bảo', 'vu.bao@hospital.vn', 'Nephrology', 18, 380000, '0901234615', NULL, 'Chuyên gia thận học, lọc máu và ghép thận', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(50, 'dang.thuy', '123', 'Đặng Thị Thúy Vân', 'dang.thuy@hospital.vn', 'Nephrology', 18, 360000, '0901234616', NULL, 'Bác sĩ nội thận, sỏi thận và viêm thận', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(51, 'ly.tan', '123', 'Lý Văn Tấn', 'ly.tan@hospital.vn', 'Pediatrics', 1, 260000, '0901234617', NULL, 'Bác sĩ nhi khoa, chuyên bệnh tiêu hóa trẻ em', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(52, 'mai.anh', '123', 'Mai Thị Ánh Tuyết', 'mai.anh@hospital.vn', 'Obstetrics_Gynecology', 2, 320000, '0901234618', NULL, 'Bác sĩ sản khoa, siêu âm thai và tầm soát', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(53, 'phan.duc', '123', 'Phan Minh Đức', 'phan.duc@hospital.vn', 'Dermatology', 3, 300000, '0901234619', NULL, 'Bác sĩ da liễu, laser và điều trị sẹo', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(54, 'cao.linh', '123', 'Cao Thị Linh Chi', 'cao.linh@hospital.vn', 'Gastroenterology', 4, 310000, '0901234620', NULL, 'Bác sĩ tiêu hóa, nội soi dạ dày và đại tràng', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(55, 'dinh.hung', '123', 'Đinh Việt Hùng', 'dinh.hung@hospital.vn', 'Rheumatology', 5, 320000, '0901234621', NULL, 'Bác sĩ cơ xương khớp, điều trị thoái hóa cột sống', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(56, 'to.nhi', '123', 'Tô Thị Như Nhi', 'to.nhi@hospital.vn', 'ENT', 8, 270000, '0901234622', NULL, 'Bác sĩ TMH, điều trị viêm tai giữa và ngạt mũi', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(57, 'lam.son', '123', 'Lâm Quốc Sơn', 'lam.son@hospital.vn', 'Oncology', 9, 480000, '0901234623', NULL, 'Bác sĩ ung bướu, phẫu thuật cắt bỏ khối u', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(58, 'vo.hanh', '123', 'Võ Thị Hạnh Phúc', 'vo.hanh@hospital.vn', 'Cardiology', 10, 330000, '0901234624', NULL, 'Bác sĩ tim mạch, can thiệp mạch vành', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(59, 'vu.phuc', '123', 'Vũ Minh Phúc', 'vu.phuc@hospital.vn', 'Orthopedics', 12, 370000, '0901234625', NULL, 'Bác sĩ chỉnh hình, phẫu thuật cột sống', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(60, 'dang.hanh', '123', 'Đặng Thị Hạnh', 'dang.hanh@hospital.vn', 'Dentistry', 16, 260000, '0901234626', NULL, 'Bác sĩ nha khoa, chỉnh nha và niềng răng', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(61, 'le.hai', '123', 'Lê Văn Hải', 'le.hai@hospital.vn', 'Allergy_Immunology', 6, 320000, '0901234601', NULL, 'Chuyên gia dị ứng, điều trị hen phế quản và viêm mũi dị ứng', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(62, 'pham.thu', '123', 'Phạm Thị Thu', 'pham.thu@hospital.vn', 'Allergy_Immunology', 6, 300000, '0901234602', NULL, 'Bác sĩ dị ứng, test và điều trị dị ứng thực phẩm', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(63, 'vu.tuan', '123', 'Vũ Anh Tuấn', 'vu.tuan@hospital.vn', 'Anesthesiology', 7, 350000, '0901234603', NULL, 'Bác sĩ gây mê hồi sức, chuyên gây mê ngoài màng cứng', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(64, 'dang.hoa', '123', 'Đặng Thị Hoa', 'dang.hoa@hospital.vn', 'Anesthesiology', 7, 330000, '0901234604', NULL, 'Bác sĩ gây mê, kiểm soát đau sau phẫu thuật', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(65, 'hoang.khang', '123', 'Hoàng Minh Khang', 'hoang.khang@hospital.vn', 'Geriatrics', 11, 280000, '0901234605', NULL, 'Bác sĩ lão khoa, chăm sóc người cao tuổi mắc nhiều bệnh mãn tính', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(66, 'ngo.phuong', '123', 'Ngô Thị Phương Nga', 'ngo.phuong@hospital.vn', 'Geriatrics', 11, 260000, '0901234606', NULL, 'Bác sĩ lão khoa, phục hồi chức năng sau đột quỵ', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(67, 'ly.thanh', '123', 'Lý Quốc Thanh', 'ly.thanh@hospital.vn', 'Emergency_Medicine', 13, 400000, '0901234607', NULL, 'Bác sĩ cấp cứu, hồi sức tim phổi', 17, 1, '2026-01-14 11:04:43', NULL, 0),
(68, 'mai.lan', '123', 'Mai Thị Lan Anh', 'mai.lan@hospital.vn', 'Emergency_Medicine', 13, 380000, '0901234608', NULL, 'Bác sĩ cấp cứu, xử lý chấn thương đa khoa', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(69, 'phan.cuong', '123', 'Phan Văn Cường', 'phan.cuong@hospital.vn', 'General_Surgery', 14, 450000, '0901234609', NULL, 'Phẫu thuật viên tổng quát, mổ nội soi ổ bụng', 18, 1, '2026-01-14 11:04:43', NULL, 0),
(70, 'cao.huyen', '123', 'Cao Thị Huyền', 'cao.huyen@hospital.vn', 'General_Surgery', 14, 420000, '0901234610', NULL, 'Bác sĩ ngoại khoa, phẫu thuật tuyến giáp và vú', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(71, 'dinh.quang', '123', 'Đinh Quang Dũng', 'dinh.quang@hospital.vn', 'Preventive_Medicine', 15, 200000, '0901234611', NULL, 'Bác sĩ y học dự phòng, tư vấn dinh dưỡng và lối sống', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(72, 'to.my', '123', 'Tô Thị Mỹ Linh', 'to.my@hospital.vn', 'Preventive_Medicine', 15, 220000, '0901234612', NULL, 'Bác sĩ y học dự phòng, tiêm chủng và tư vấn sức khỏe', 8, 1, '2026-01-14 11:04:43', NULL, 0),
(73, 'lam.duy', '123', 'Lâm Văn Duy', 'lam.duy@hospital.vn', 'Infectious_Disease', 17, 350000, '0901234613', NULL, 'Bác sĩ truyền nhiễm, điều trị sốt xuất huyết và sốt rét', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(74, 'vo.chi', '123', 'Võ Thị Chi', 'vo.chi@hospital.vn', 'Infectious_Disease', 17, 330000, '0901234614', NULL, 'Bác sĩ truyền nhiễm, HIV/AIDS và viêm gan virus', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(75, 'vu.bao', '123', 'Vũ Quốc Bảo', 'vu.bao@hospital.vn', 'Nephrology', 18, 380000, '0901234615', NULL, 'Chuyên gia thận học, lọc máu và ghép thận', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(76, 'dang.thuy', '123', 'Đặng Thị Thúy Vân', 'dang.thuy@hospital.vn', 'Nephrology', 18, 360000, '0901234616', NULL, 'Bác sĩ nội thận, sỏi thận và viêm thận', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(77, 'ly.tan', '123', 'Lý Văn Tấn', 'ly.tan@hospital.vn', 'Pediatrics', 1, 260000, '0901234617', NULL, 'Bác sĩ nhi khoa, chuyên bệnh tiêu hóa trẻ em', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(78, 'mai.anh', '123', 'Mai Thị Ánh Tuyết', 'mai.anh@hospital.vn', 'Obstetrics_Gynecology', 2, 320000, '0901234618', NULL, 'Bác sĩ sản khoa, siêu âm thai và tầm soát', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(79, 'phan.duc', '123', 'Phan Minh Đức', 'phan.duc@hospital.vn', 'Dermatology', 3, 300000, '0901234619', NULL, 'Bác sĩ da liễu, laser và điều trị sẹo', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(80, 'cao.linh', '123', 'Cao Thị Linh Chi', 'cao.linh@hospital.vn', 'Gastroenterology', 4, 310000, '0901234620', NULL, 'Bác sĩ tiêu hóa, nội soi dạ dày và đại tràng', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(81, 'dinh.hung', '123', 'Đinh Việt Hùng', 'dinh.hung@hospital.vn', 'Rheumatology', 5, 320000, '0901234621', NULL, 'Bác sĩ cơ xương khớp, điều trị thoái hóa cột sống', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(82, 'to.nhi', '123', 'Tô Thị Như Nhi', 'to.nhi@hospital.vn', 'ENT', 8, 270000, '0901234622', NULL, 'Bác sĩ TMH, điều trị viêm tai giữa và ngạt mũi', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(83, 'lam.son', '123', 'Lâm Quốc Sơn', 'lam.son@hospital.vn', 'Oncology', 9, 480000, '0901234623', NULL, 'Bác sĩ ung bướu, phẫu thuật cắt bỏ khối u', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(84, 'vo.hanh', '123', 'Võ Thị Hạnh Phúc', 'vo.hanh@hospital.vn', 'Cardiology', 10, 330000, '0901234624', NULL, 'Bác sĩ tim mạch, can thiệp mạch vành', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(85, 'vu.phuc', '123', 'Vũ Minh Phúc', 'vu.phuc@hospital.vn', 'Orthopedics', 12, 370000, '0901234625', NULL, 'Bác sĩ chỉnh hình, phẫu thuật cột sống', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(86, 'dang.hanh', '123', 'Đặng Thị Hạnh', 'dang.hanh@hospital.vn', 'Dentistry', 16, 260000, '0901234626', NULL, 'Bác sĩ nha khoa, chỉnh nha và niềng răng', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(87, 'nguyen.khanh', '123', 'Nguyễn Thị Khánh Linh', 'nguyen.khanh@hospital.vn', 'Allergy_Immunology', 6, 310000, '0901234627', NULL, 'Bác sĩ dị ứng - miễn dịch, chuyên điều trị viêm da cơ địa', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(88, 'tran.binh', '123', 'Trần Văn Bình', 'tran.binh2@hospital.vn', 'Anesthesiology', 7, 340000, '0901234628', NULL, 'Bác sĩ gây mê, chuyên gây tê vùng và giảm đau', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(89, 'le.phuong', '123', 'Lê Thị Phương Anh', 'le.phuong@hospital.vn', 'Geriatrics', 11, 270000, '0901234629', NULL, 'Bác sĩ lão khoa, chuyên điều trị sa sút trí tuệ và Parkinson', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(90, 'pham.quoc', '123', 'Phạm Quốc Huy', 'pham.quoc@hospital.vn', 'Emergency_Medicine', 13, 390000, '0901234630', NULL, 'Bác sĩ cấp cứu, chuyên xử lý ngộ độc và sốc', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(91, 'vu.minh', '123', 'Vũ Minh Tuấn', 'vu.minh@hospital.vn', 'General_Surgery', 14, 430000, '0901234631', NULL, 'Phẫu thuật viên, chuyên phẫu thuật ruột thừa và sỏi mật', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(92, 'dang.mai', '123', 'Đặng Thị Mai Linh', 'dang.mai@hospital.vn', 'Preventive_Medicine', 15, 210000, '0901234632', NULL, 'Bác sĩ y học dự phòng, tư vấn phòng bệnh không lây', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(93, 'hoang.nam', '123', 'Hoàng Văn Nam', 'hoang.nam@hospital.vn', 'Infectious_Disease', 17, 340000, '0901234633', NULL, 'Bác sĩ truyền nhiễm, điều trị lao và bệnh nhiệt đới', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(94, 'ngo.lan', '123', 'Ngô Thị Lan Hương', 'ngo.lan@hospital.vn', 'Nephrology', 18, 370000, '0901234634', NULL, 'Bác sĩ nội thận, điều trị suy thận mãn và thận hư', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(95, 'ly.hong', '123', 'Lý Thị Hồng Nhung', 'ly.hong@hospital.vn', 'Endocrinology', 19, 330000, '0901234635', NULL, 'Bác sĩ nội tiết, chuyên bệnh tuyến yên và tuyến thượng thận', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(96, 'mai.tung', '123', 'Mai Văn Tùng', 'mai.tung@hospital.vn', 'Psychiatry', 20, 380000, '0901234636', NULL, 'Bác sĩ tâm thần, điều trị rối loạn lưỡng cực và tâm thần phân liệt', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(97, 'nguyen.thao', '123', 'Nguyễn Thị Thảo', 'nguyen.thao@hospital.vn', 'Laboratory', 22, 150000, '0901234640', NULL, 'Bác sĩ xét nghiệm, chuyên sinh hóa và huyết học', 8, 1, '2026-01-14 11:04:43', NULL, 0),
(98, 'tran.duc', '123', 'Trần Văn Đức', 'tran.duc@hospital.vn', 'Laboratory', 22, 160000, '0901234641', NULL, 'Bác sĩ xét nghiệm, vi sinh và miễn dịch', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(99, 'le.huyen', '123', 'Lê Thị Huyền', 'le.huyen@hospital.vn', 'Laboratory', 22, 155000, '0901234642', NULL, 'Bác sĩ xét nghiệm lâm sàng, giải phẫu bệnh', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(100, 'pham.long', '123', 'Phạm Văn Long', 'pham.long@hospital.vn', 'Hematology', 23, 380000, '0901234643', NULL, 'Chuyên gia huyết học, điều trị bạch cầu và ung thư máu', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(101, 'vu.nga', '123', 'Vũ Thị Nga', 'vu.nga@hospital.vn', 'Hematology', 23, 360000, '0901234644', NULL, 'Bác sĩ huyết học, rối loạn đông máu và thiếu máu', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(102, 'dang.khoa', '123', 'Đặng Minh Khoa', 'dang.khoa@hospital.vn', 'Hematology', 23, 370000, '0901234645', NULL, 'Bác sĩ huyết học, ghép tủy xương', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(103, 'hoang.phuc', '123', 'Hoàng Văn Phúc', 'hoang.phuc@hospital.vn', 'Plastic_Surgery', 24, 500000, '0901234646', NULL, 'Phẫu thuật viên thẩm mỹ, nâng ngực và hút mỡ', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(104, 'ngo.thuy', '123', 'Ngô Thị Thúy Kiều', 'ngo.thuy@hospital.vn', 'Plastic_Surgery', 24, 480000, '0901234647', NULL, 'Bác sĩ phẫu thuật thẩm mỹ, nâng mũi và cắt mí', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(105, 'ly.cuong', '123', 'Lý Văn Cường', 'ly.cuong@hospital.vn', 'Plastic_Surgery', 24, 520000, '0901234648', NULL, 'Phẫu thuật tạo hình, tái tạo sau tai nạn và bỏng', 18, 1, '2026-01-14 11:04:43', NULL, 0),
(106, 'mai.phuong', '123', 'Mai Thị Phương', 'mai.phuong@hospital.vn', 'Speech_Therapy', 26, 250000, '0901234649', NULL, 'Chuyên gia ngôn ngữ trị liệu, rối loạn nói ở trẻ em', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(107, 'phan.hai', '123', 'Phan Văn Hải', 'phan.hai@hospital.vn', 'Speech_Therapy', 26, 240000, '0901234650', NULL, 'Bác sĩ ngôn ngữ trị liệu, phục hồi sau đột quỵ', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(108, 'cao.uyen', '123', 'Cao Thị Uyển', 'cao.uyen@hospital.vn', 'Speech_Therapy', 26, 260000, '0901234651', NULL, 'Chuyên gia trị liệu, rối loạn nuốt và giao tiếp', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(109, 'dinh.tuan', '123', 'Đinh Anh Tuấn', 'dinh.tuan@hospital.vn', 'Rehabilitation', 27, 280000, '0901234652', NULL, 'Bác sĩ phục hồi chức năng, vật lý trị liệu sau chấn thương', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(110, 'to.anh', '123', 'Tô Thị Ánh', 'to.anh@hospital.vn', 'Rehabilitation', 27, 270000, '0901234653', NULL, 'Bác sĩ PHCN, phục hồi sau phẫu thuật xương khớp', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(111, 'lam.quang', '123', 'Lâm Quang Huy', 'lam.quang@hospital.vn', 'Rehabilitation', 27, 290000, '0901234654', NULL, 'Chuyên gia phục hồi chức năng, bại liệt và tai biến', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(112, 'vo.thanh', '123', 'Võ Văn Thanh', 'vo.thanh@hospital.vn', 'Fertility', 28, 450000, '0901234655', NULL, 'Chuyên gia vô sinh, thụ tinh trong ống nghiệm IVF', 17, 1, '2026-01-14 11:04:43', NULL, 0),
(113, 'vu.linh', '123', 'Vũ Thị Linh Đan', 'vu.linh@hospital.vn', 'Fertility', 28, 430000, '0901234656', NULL, 'Bác sĩ vô sinh, siêu âm buồng trứng và điều trị hormone', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(114, 'dang.hieu', '123', 'Đặng Văn Hiếu', 'dang.hieu@hospital.vn', 'Fertility', 28, 460000, '0901234657', NULL, 'Chuyên gia hỗ trợ sinh sản, điều trị hiếm muộn', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(115, 'ly.thu', '123', 'Lý Thị Thu Hà', 'ly.thu@hospital.vn', 'Tuberculosis', 30, 300000, '0901234658', NULL, 'Bác sĩ lao phổi, điều trị lao và viêm phổi mãn tính', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(116, 'mai.son', '123', 'Mai Văn Sơn', 'mai.son@hospital.vn', 'Tuberculosis', 30, 310000, '0901234659', NULL, 'Bác sĩ bệnh phổi, COPD và hen phế quản', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(117, 'phan.lan', '123', 'Phan Thị Lan', 'phan.lan@hospital.vn', 'Tuberculosis', 30, 320000, '0901234660', NULL, 'Chuyên gia lao, điều trị lao kháng thuốc', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(118, 'cao.dat', '123', 'Cao Minh Đạt', 'cao.dat@hospital.vn', 'Sports_Medicine', 31, 350000, '0901234661', NULL, 'Bác sĩ thể thao, chấn thương và phục hồi vận động viên', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(119, 'dinh.thao', '123', 'Đinh Thị Thảo', 'dinh.thao@hospital.vn', 'Sports_Medicine', 31, 340000, '0901234662', NULL, 'Bác sĩ y học thể thao, dinh dưỡng và tăng cường thể lực', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(120, 'to.khanh', '123', 'Tô Văn Khánh', 'to.khanh@hospital.vn', 'Sports_Medicine', 31, 360000, '0901234663', NULL, 'Chuyên gia y học thể thao, phòng ngừa chấn thương', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(121, 'lam.hoang', '123', 'Lâm Văn Hoàng', 'lam.hoang@hospital.vn', 'Andrology', 33, 380000, '0901234664', NULL, 'Bác sĩ nam khoa, rối loạn cương và vô sinh nam', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(122, 'vo.dung', '123', 'Võ Thị Dung', 'vo.dung@hospital.vn', 'Andrology', 33, 370000, '0901234665', NULL, 'Bác sĩ nam khoa, viêm tuyến tiền liệt', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(123, 'vu.tien', '123', 'Vũ Văn Tiến', 'vu.tien@hospital.vn', 'Andrology', 33, 390000, '0901234666', NULL, 'Chuyên gia nam khoa, hormone và sức khỏe nam giới', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(124, 'dang.tung', '123', 'Đặng Văn Tùng', 'dang.tung@hospital.vn', 'Urology', 34, 420000, '0901234667', NULL, 'Phẫu thuật viên tiết niệu, sỏi thận và u bàng quang', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(125, 'ly.huong', '123', 'Lý Thị Hương Giang', 'ly.huong@hospital.vn', 'Urology', 34, 400000, '0901234668', NULL, 'Bác sĩ ngoại tiết niệu, phẫu thuật nội soi', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(126, 'mai.quan', '123', 'Mai Văn Quân', 'mai.quan@hospital.vn', 'Urology', 34, 430000, '0901234669', NULL, 'Phẫu thuật viên tiết niệu, ung thư tiền liệt tuyến', 17, 1, '2026-01-14 11:04:43', NULL, 0),
(127, 'phan.minh', '123', 'Phan Minh Nhật', 'phan.minh@hospital.vn', 'Radiology', 35, 280000, '0901234670', NULL, 'Bác sĩ chẩn đoán hình ảnh, siêu âm và CT', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(128, 'cao.van', '123', 'Cao Thị Vân Anh', 'cao.van@hospital.vn', 'Radiology', 35, 290000, '0901234671', NULL, 'Bác sĩ X-quang, MRI và chẩn đoán qua hình ảnh', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(129, 'dinh.bao', '123', 'Đinh Quốc Bảo', 'dinh.bao@hospital.vn', 'Radiology', 35, 300000, '0901234672', NULL, 'Chuyên gia chẩn đoán hình ảnh, can thiệp mạch máu', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(130, 'to.hai', '123', 'Tô Văn Hải', 'to.hai@hospital.vn', 'Neurosurgery', 36, 550000, '0901234673', NULL, 'Phẫu thuật viên thần kinh, u não và thoát vị đĩa đệm', 18, 1, '2026-01-14 11:04:43', NULL, 0),
(131, 'lam.thu', '123', 'Lâm Thị Thu', 'lam.thu@hospital.vn', 'Neurosurgery', 36, 530000, '0901234674', NULL, 'Bác sĩ ngoại thần kinh, phẫu thuật cột sống', 16, 1, '2026-01-14 11:04:43', NULL, 0),
(132, 'vo.quan', '123', 'Võ Văn Quân', 'vo.quan@hospital.vn', 'Neurosurgery', 36, 570000, '0901234675', NULL, 'Giáo sư ngoại thần kinh, phẫu thuật não và mạch máu não', 20, 1, '2026-01-14 11:04:43', NULL, 0),
(133, 'vu.duy', '123', 'Vũ Văn Duy', 'vu.duy@hospital.vn', 'Thoracic_Cardiovascular_Surgery', 37, 600000, '0901234676', NULL, 'Phẫu thuật viên tim mạch, ghép tim và bypass', 19, 1, '2026-01-14 11:04:43', NULL, 0),
(134, 'dang.nhi', '123', 'Đặng Thị Nhi', 'dang.nhi@hospital.vn', 'Thoracic_Cardiovascular_Surgery', 37, 580000, '0901234677', NULL, 'Bác sĩ ngoại lồng ngực, phẫu thuật phổi', 17, 1, '2026-01-14 11:04:43', NULL, 0),
(135, 'ly.nam', '123', 'Lý Văn Nam', 'ly.nam@hospital.vn', 'Thoracic_Cardiovascular_Surgery', 37, 620000, '0901234678', NULL, 'Chuyên gia phẫu thuật tim mạch, van tim', 21, 1, '2026-01-14 11:04:43', NULL, 0),
(136, 'mai.duc', '123', 'Mai Văn Đức', 'mai.duc@hospital.vn', 'Urology_Internal', 38, 350000, '0901234679', NULL, 'Bác sĩ ngoại niệu, viêm bàng quang và tiết niệu', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(137, 'phan.thu', '123', 'Phan Thị Thu Hà', 'phan.thu@hospital.vn', 'Urology_Internal', 38, 360000, '0901234680', NULL, 'Bác sĩ ngoại niệu, sỏi tiết niệu và phẫu thuật', 13, 1, '2026-01-14 11:04:43', NULL, 0),
(138, 'cao.tuan', '123', 'Cao Anh Tuấn', 'cao.tuan@hospital.vn', 'Urology_Internal', 38, 370000, '0901234681', NULL, 'Bác sĩ ngoại niệu, nội soi và điều trị', 14, 1, '2026-01-14 11:04:43', NULL, 0),
(139, 'dinh.lan', '123', 'Đinh Thị Lan', 'dinh.lan@hospital.vn', 'Nutrition', 39, 200000, '0901234682', NULL, 'Chuyên gia dinh dưỡng, giảm cân và tiểu đường', 10, 1, '2026-01-14 11:04:43', NULL, 0),
(140, 'to.long', '123', 'Tô Văn Long', 'to.long@hospital.vn', 'Nutrition', 39, 210000, '0901234683', NULL, 'Bác sĩ dinh dưỡng, chế độ ăn cho bệnh nhân nội khoa', 9, 1, '2026-01-14 11:04:43', NULL, 0),
(141, 'lam.nga', '123', 'Lâm Thị Nga', 'lam.nga@hospital.vn', 'Nutrition', 39, 220000, '0901234684', NULL, 'Chuyên gia dinh dưỡng lâm sàng, dinh dưỡng trẻ em', 11, 1, '2026-01-14 11:04:43', NULL, 0),
(148, 'vo.binh', '123', 'Võ Văn Bình', 'vo.binh@hospital.vn', 'Pain_Management', 42, 380000, '0901234685', NULL, 'Chuyên gia điều trị đau, đau mãn tính và ung thư', 15, 1, '2026-01-14 11:04:43', NULL, 0),
(149, 'vu.mai', '123', 'Vũ Thị Mai', 'vu.mai@hospital.vn', 'Pain_Management', 42, 370000, '0901234686', NULL, 'Bác sĩ điều trị đau, tiêm khớp và giảm đau', 12, 1, '2026-01-14 11:04:43', NULL, 0),
(150, 'dang.phong', '123', 'Đặng Văn Phong', 'dang.phong@hospital.vn', 'Pain_Management', 42, 390000, '0901234687', NULL, 'Chuyên gia quản lý đau, đau thần kinh', 16, 1, '2026-01-14 11:04:43', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_ratings`
--

CREATE TABLE `doctor_ratings` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `rating` int(1) NOT NULL COMMENT '1-5',
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_ratings`
--

INSERT INTO `doctor_ratings` (`id`, `doctor_id`, `patient_id`, `appointment_id`, `rating`, `review`, `created_at`) VALUES
(1, 1, 1, 1, 5, 'Bác sĩ rất nhiệt tình', '2026-01-27 03:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=CN, 1=T2, 2=T3, 3=T4, 4=T5, 5=T6, 6=T7',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int(3) DEFAULT 30 COMMENT 'Thời gian mỗi slot (phút)',
  `max_patients` int(3) DEFAULT 1 COMMENT 'Số bệnh nhân tối đa mỗi slot',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`, `max_patients`, `is_active`) VALUES
(1, 1, 1, '08:00:00', '17:00:00', 30, 1, 1),
(2, 2, 1, '08:00:00', '17:00:00', 30, 1, 1),
(3, 3, 1, '08:00:00', '17:00:00', 30, 1, 1),
(4, 4, 1, '08:00:00', '17:00:00', 30, 1, 1),
(5, 5, 1, '08:00:00', '17:00:00', 30, 1, 1),
(6, 6, 1, '08:00:00', '17:00:00', 30, 1, 1),
(7, 7, 1, '08:00:00', '17:00:00', 30, 1, 1),
(8, 8, 1, '08:00:00', '17:00:00', 30, 1, 1),
(9, 9, 1, '08:00:00', '17:00:00', 30, 1, 1),
(10, 10, 1, '08:00:00', '17:00:00', 30, 1, 1),
(11, 11, 1, '08:00:00', '17:00:00', 30, 1, 1),
(12, 12, 1, '08:00:00', '17:00:00', 30, 1, 1),
(13, 13, 1, '08:00:00', '17:00:00', 30, 1, 1),
(14, 14, 1, '08:00:00', '17:00:00', 30, 1, 1),
(15, 15, 1, '08:00:00', '17:00:00', 30, 1, 1),
(16, 16, 1, '08:00:00', '17:00:00', 30, 1, 1),
(17, 17, 1, '08:00:00', '17:00:00', 30, 1, 1),
(18, 18, 1, '08:00:00', '17:00:00', 30, 1, 1),
(19, 19, 1, '08:00:00', '17:00:00', 30, 1, 1),
(20, 20, 1, '08:00:00', '17:00:00', 30, 1, 1),
(21, 21, 1, '08:00:00', '17:00:00', 30, 1, 1),
(22, 22, 1, '08:00:00', '17:00:00', 30, 1, 1),
(23, 23, 1, '08:00:00', '17:00:00', 30, 1, 1),
(24, 24, 1, '08:00:00', '17:00:00', 30, 1, 1),
(25, 25, 1, '08:00:00', '17:00:00', 30, 1, 1),
(26, 26, 1, '08:00:00', '17:00:00', 30, 1, 1),
(27, 27, 1, '08:00:00', '17:00:00', 30, 1, 1),
(28, 28, 1, '08:00:00', '17:00:00', 30, 1, 1),
(29, 29, 1, '08:00:00', '17:00:00', 30, 1, 1),
(30, 30, 1, '08:00:00', '17:00:00', 30, 1, 1),
(31, 31, 1, '08:00:00', '17:00:00', 30, 1, 1),
(32, 32, 1, '08:00:00', '17:00:00', 30, 1, 1),
(33, 33, 1, '08:00:00', '17:00:00', 30, 1, 1),
(34, 34, 1, '08:00:00', '17:00:00', 30, 1, 1),
(35, 1, 2, '08:00:00', '17:00:00', 30, 1, 1),
(36, 2, 2, '08:00:00', '17:00:00', 30, 1, 1),
(37, 3, 2, '08:00:00', '17:00:00', 30, 1, 1),
(38, 4, 2, '08:00:00', '17:00:00', 30, 1, 1),
(39, 5, 2, '08:00:00', '17:00:00', 30, 1, 1),
(40, 6, 2, '08:00:00', '17:00:00', 30, 1, 1),
(41, 7, 2, '08:00:00', '17:00:00', 30, 1, 1),
(42, 8, 2, '08:00:00', '17:00:00', 30, 1, 1),
(43, 9, 2, '08:00:00', '17:00:00', 30, 1, 1),
(44, 10, 2, '08:00:00', '17:00:00', 30, 1, 1),
(45, 11, 2, '08:00:00', '17:00:00', 30, 1, 1),
(46, 12, 2, '08:00:00', '17:00:00', 30, 1, 1),
(47, 13, 2, '08:00:00', '17:00:00', 30, 1, 1),
(48, 14, 2, '08:00:00', '17:00:00', 30, 1, 1),
(49, 15, 2, '08:00:00', '17:00:00', 30, 1, 1),
(50, 16, 2, '08:00:00', '17:00:00', 30, 1, 1),
(51, 17, 2, '08:00:00', '17:00:00', 30, 1, 1),
(52, 18, 2, '08:00:00', '17:00:00', 30, 1, 1),
(53, 19, 2, '08:00:00', '17:00:00', 30, 1, 1),
(54, 20, 2, '08:00:00', '17:00:00', 30, 1, 1),
(55, 21, 2, '08:00:00', '17:00:00', 30, 1, 1),
(56, 22, 2, '08:00:00', '17:00:00', 30, 1, 1),
(57, 23, 2, '08:00:00', '17:00:00', 30, 1, 1),
(58, 24, 2, '08:00:00', '17:00:00', 30, 1, 1),
(59, 25, 2, '08:00:00', '17:00:00', 30, 1, 1),
(60, 26, 2, '08:00:00', '17:00:00', 30, 1, 1),
(61, 27, 2, '08:00:00', '17:00:00', 30, 1, 1),
(62, 28, 2, '08:00:00', '17:00:00', 30, 1, 1),
(63, 29, 2, '08:00:00', '17:00:00', 30, 1, 1),
(64, 30, 2, '08:00:00', '17:00:00', 30, 1, 1),
(65, 31, 2, '08:00:00', '17:00:00', 30, 1, 1),
(66, 32, 2, '08:00:00', '17:00:00', 30, 1, 1),
(67, 33, 2, '08:00:00', '17:00:00', 30, 1, 1),
(68, 34, 2, '08:00:00', '17:00:00', 30, 1, 1),
(69, 1, 3, '08:00:00', '17:00:00', 30, 1, 1),
(70, 2, 3, '08:00:00', '17:00:00', 30, 1, 1),
(71, 3, 3, '08:00:00', '17:00:00', 30, 1, 1),
(72, 4, 3, '08:00:00', '17:00:00', 30, 1, 1),
(73, 5, 3, '08:00:00', '17:00:00', 30, 1, 1),
(74, 6, 3, '08:00:00', '17:00:00', 30, 1, 1),
(75, 7, 3, '08:00:00', '17:00:00', 30, 1, 1),
(76, 8, 3, '08:00:00', '17:00:00', 30, 1, 1),
(77, 9, 3, '08:00:00', '17:00:00', 30, 1, 1),
(78, 10, 3, '08:00:00', '17:00:00', 30, 1, 1),
(79, 11, 3, '08:00:00', '17:00:00', 30, 1, 1),
(80, 12, 3, '08:00:00', '17:00:00', 30, 1, 1),
(81, 13, 3, '08:00:00', '17:00:00', 30, 1, 1),
(82, 14, 3, '08:00:00', '17:00:00', 30, 1, 1),
(83, 15, 3, '08:00:00', '17:00:00', 30, 1, 1),
(84, 16, 3, '08:00:00', '17:00:00', 30, 1, 1),
(85, 17, 3, '08:00:00', '17:00:00', 30, 1, 1),
(86, 18, 3, '08:00:00', '17:00:00', 30, 1, 1),
(87, 19, 3, '08:00:00', '17:00:00', 30, 1, 1),
(88, 20, 3, '08:00:00', '17:00:00', 30, 1, 1),
(89, 21, 3, '08:00:00', '17:00:00', 30, 1, 1),
(90, 22, 3, '08:00:00', '17:00:00', 30, 1, 1),
(91, 23, 3, '08:00:00', '17:00:00', 30, 1, 1),
(92, 24, 3, '08:00:00', '17:00:00', 30, 1, 1),
(93, 25, 3, '08:00:00', '17:00:00', 30, 1, 1),
(94, 26, 3, '08:00:00', '17:00:00', 30, 1, 1),
(95, 27, 3, '08:00:00', '17:00:00', 30, 1, 1),
(96, 28, 3, '08:00:00', '17:00:00', 30, 1, 1),
(97, 29, 3, '08:00:00', '17:00:00', 30, 1, 1),
(98, 30, 3, '08:00:00', '17:00:00', 30, 1, 1),
(99, 31, 3, '08:00:00', '17:00:00', 30, 1, 1),
(100, 32, 3, '08:00:00', '17:00:00', 30, 1, 1),
(101, 33, 3, '08:00:00', '17:00:00', 30, 1, 1),
(102, 34, 3, '08:00:00', '17:00:00', 30, 1, 1),
(103, 1, 4, '08:00:00', '17:00:00', 30, 1, 1),
(104, 2, 4, '08:00:00', '17:00:00', 30, 1, 1),
(105, 3, 4, '08:00:00', '17:00:00', 30, 1, 1),
(106, 4, 4, '08:00:00', '17:00:00', 30, 1, 1),
(107, 5, 4, '08:00:00', '17:00:00', 30, 1, 1),
(108, 6, 4, '08:00:00', '17:00:00', 30, 1, 1),
(109, 7, 4, '08:00:00', '17:00:00', 30, 1, 1),
(110, 8, 4, '08:00:00', '17:00:00', 30, 1, 1),
(111, 9, 4, '08:00:00', '17:00:00', 30, 1, 1),
(112, 10, 4, '08:00:00', '17:00:00', 30, 1, 1),
(113, 11, 4, '08:00:00', '17:00:00', 30, 1, 1),
(114, 12, 4, '08:00:00', '17:00:00', 30, 1, 1),
(115, 13, 4, '08:00:00', '17:00:00', 30, 1, 1),
(116, 14, 4, '08:00:00', '17:00:00', 30, 1, 1),
(117, 15, 4, '08:00:00', '17:00:00', 30, 1, 1),
(118, 16, 4, '08:00:00', '17:00:00', 30, 1, 1),
(119, 17, 4, '08:00:00', '17:00:00', 30, 1, 1),
(120, 18, 4, '08:00:00', '17:00:00', 30, 1, 1),
(121, 19, 4, '08:00:00', '17:00:00', 30, 1, 1),
(122, 20, 4, '08:00:00', '17:00:00', 30, 1, 1),
(123, 21, 4, '08:00:00', '17:00:00', 30, 1, 1),
(124, 22, 4, '08:00:00', '17:00:00', 30, 1, 1),
(125, 23, 4, '08:00:00', '17:00:00', 30, 1, 1),
(126, 24, 4, '08:00:00', '17:00:00', 30, 1, 1),
(127, 25, 4, '08:00:00', '17:00:00', 30, 1, 1),
(128, 26, 4, '08:00:00', '17:00:00', 30, 1, 1),
(129, 27, 4, '08:00:00', '17:00:00', 30, 1, 1),
(130, 28, 4, '08:00:00', '17:00:00', 30, 1, 1),
(131, 29, 4, '08:00:00', '17:00:00', 30, 1, 1),
(132, 30, 4, '08:00:00', '17:00:00', 30, 1, 1),
(133, 31, 4, '08:00:00', '17:00:00', 30, 1, 1),
(134, 32, 4, '08:00:00', '17:00:00', 30, 1, 1),
(135, 33, 4, '08:00:00', '17:00:00', 30, 1, 1),
(136, 34, 4, '08:00:00', '17:00:00', 30, 1, 1),
(137, 1, 5, '08:00:00', '17:00:00', 30, 1, 1),
(138, 2, 5, '08:00:00', '17:00:00', 30, 1, 1),
(139, 3, 5, '08:00:00', '17:00:00', 30, 1, 1),
(140, 4, 5, '08:00:00', '17:00:00', 30, 1, 1),
(141, 5, 5, '08:00:00', '17:00:00', 30, 1, 1),
(142, 6, 5, '08:00:00', '17:00:00', 30, 1, 1),
(143, 7, 5, '08:00:00', '17:00:00', 30, 1, 1),
(144, 8, 5, '08:00:00', '17:00:00', 30, 1, 1),
(145, 9, 5, '08:00:00', '17:00:00', 30, 1, 1),
(146, 10, 5, '08:00:00', '17:00:00', 30, 1, 1),
(147, 11, 5, '08:00:00', '17:00:00', 30, 1, 1),
(148, 12, 5, '08:00:00', '17:00:00', 30, 1, 1),
(149, 13, 5, '08:00:00', '17:00:00', 30, 1, 1),
(150, 14, 5, '08:00:00', '17:00:00', 30, 1, 1),
(151, 15, 5, '08:00:00', '17:00:00', 30, 1, 1),
(152, 16, 5, '08:00:00', '17:00:00', 30, 1, 1),
(153, 17, 5, '08:00:00', '17:00:00', 30, 1, 1),
(154, 18, 5, '08:00:00', '17:00:00', 30, 1, 1),
(155, 19, 5, '08:00:00', '17:00:00', 30, 1, 1),
(156, 20, 5, '08:00:00', '17:00:00', 30, 1, 1),
(157, 21, 5, '08:00:00', '17:00:00', 30, 1, 1),
(158, 22, 5, '08:00:00', '17:00:00', 30, 1, 1),
(159, 23, 5, '08:00:00', '17:00:00', 30, 1, 1),
(160, 24, 5, '08:00:00', '17:00:00', 30, 1, 1),
(161, 25, 5, '08:00:00', '17:00:00', 30, 1, 1),
(162, 26, 5, '08:00:00', '17:00:00', 30, 1, 1),
(163, 27, 5, '08:00:00', '17:00:00', 30, 1, 1),
(164, 28, 5, '08:00:00', '17:00:00', 30, 1, 1),
(165, 29, 5, '08:00:00', '17:00:00', 30, 1, 1),
(166, 30, 5, '08:00:00', '17:00:00', 30, 1, 1),
(167, 31, 5, '08:00:00', '17:00:00', 30, 1, 1),
(168, 32, 5, '08:00:00', '17:00:00', 30, 1, 1),
(169, 33, 5, '08:00:00', '17:00:00', 30, 1, 1),
(170, 34, 5, '08:00:00', '17:00:00', 30, 1, 1),
(171, 1, 6, '08:00:00', '12:00:00', 30, 1, 1),
(172, 2, 6, '08:00:00', '12:00:00', 30, 1, 1),
(173, 3, 6, '08:00:00', '12:00:00', 30, 1, 1),
(174, 4, 6, '08:00:00', '12:00:00', 30, 1, 1),
(175, 5, 6, '08:00:00', '12:00:00', 30, 1, 1),
(176, 6, 6, '08:00:00', '12:00:00', 30, 1, 1),
(177, 7, 6, '08:00:00', '12:00:00', 30, 1, 1),
(178, 8, 6, '08:00:00', '12:00:00', 30, 1, 1),
(179, 9, 6, '08:00:00', '12:00:00', 30, 1, 1),
(180, 10, 6, '08:00:00', '12:00:00', 30, 1, 1),
(181, 11, 6, '08:00:00', '12:00:00', 30, 1, 1),
(182, 12, 6, '08:00:00', '12:00:00', 30, 1, 1),
(183, 13, 6, '08:00:00', '12:00:00', 30, 1, 1),
(184, 14, 6, '08:00:00', '12:00:00', 30, 1, 1),
(185, 15, 6, '08:00:00', '12:00:00', 30, 1, 1),
(186, 16, 6, '08:00:00', '12:00:00', 30, 1, 1),
(187, 17, 6, '08:00:00', '12:00:00', 30, 1, 1),
(188, 18, 6, '08:00:00', '12:00:00', 30, 1, 1),
(189, 19, 6, '08:00:00', '12:00:00', 30, 1, 1),
(190, 20, 6, '08:00:00', '12:00:00', 30, 1, 1),
(191, 21, 6, '08:00:00', '12:00:00', 30, 1, 1),
(192, 22, 6, '08:00:00', '12:00:00', 30, 1, 1),
(193, 23, 6, '08:00:00', '12:00:00', 30, 1, 1),
(194, 24, 6, '08:00:00', '12:00:00', 30, 1, 1),
(195, 25, 6, '08:00:00', '12:00:00', 30, 1, 1),
(196, 26, 6, '08:00:00', '12:00:00', 30, 1, 1),
(197, 27, 6, '08:00:00', '12:00:00', 30, 1, 1),
(198, 28, 6, '08:00:00', '12:00:00', 30, 1, 1),
(199, 29, 6, '08:00:00', '12:00:00', 30, 1, 1),
(200, 30, 6, '08:00:00', '12:00:00', 30, 1, 1),
(201, 31, 6, '08:00:00', '12:00:00', 30, 1, 1),
(202, 32, 6, '08:00:00', '12:00:00', 30, 1, 1),
(203, 33, 6, '08:00:00', '12:00:00', 30, 1, 1),
(204, 34, 6, '08:00:00', '12:00:00', 30, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `forum_attachments`
--

CREATE TABLE `forum_attachments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) DEFAULT NULL COMMENT 'Size in bytes',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `content` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'For nested replies',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_comments`
--

INSERT INTO `forum_comments` (`id`, `post_id`, `user_id`, `user_type`, `content`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'admin', '123', NULL, '2026-01-27 02:43:19', '2026-01-27 02:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `forum_likes`
--

CREATE TABLE `forum_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `target_id` int(11) NOT NULL,
  `target_type` enum('post','comment') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_likes`
--

INSERT INTO `forum_likes` (`id`, `user_id`, `user_type`, `target_id`, `target_type`, `created_at`) VALUES
(2, 1, 'patient', 2, 'post', '2026-01-27 03:17:18'),
(4, 1, 'patient', 1, 'post', '2026-02-03 14:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `category` enum('general','question','discussion','announcement') DEFAULT 'general',
  `status` enum('open','closed','solved') DEFAULT 'open',
  `privacy` enum('public','private') DEFAULT 'public',
  `views` int(11) DEFAULT 0,
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `user_id`, `user_type`, `title`, `content`, `tags`, `category`, `status`, `privacy`, `views`, `is_pinned`, `created_at`, `updated_at`) VALUES
(1, 4, 'patient', 'Câu hỏi về lịch hẹn khám tim mạch', 'Xin chào, tôi muốn hỏi về quy trình đặt lịch khám tim mạch. Tôi cần chuẩn bị gì trước khi đến khám?', '#tim-mạch,#câu-hỏi', 'question', 'open', 'public', 23, 0, '2026-01-27 01:28:05', '2026-02-03 14:08:25'),
(2, 1, 'patient', 'Chia sẻ kinh nghiệm khám tại Global Hospital', 'Tôi vừa khám xong tại bệnh viện, cảm thấy rất hài lòng với dịch vụ. Bác sĩ tận tâm và chuyên nghiệp.', '#chia-sẻ,#kinh-nghiệm', 'discussion', 'open', 'public', 7, 0, '2026-01-27 01:28:05', '2026-02-01 16:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `medical_attachments`
--

CREATE TABLE `medical_attachments` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL COMMENT 'image, pdf, doc, xray, etc',
  `file_size` int(11) DEFAULT NULL COMMENT 'Kích thước file (bytes)',
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_documents`
--

CREATE TABLE `medical_documents` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `doctor` varchar(50) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `record_date` date NOT NULL,
  `record_type` enum('consultation','checkup','emergency','followup','surgery') DEFAULT 'consultation',
  `height` decimal(5,2) DEFAULT NULL COMMENT 'Chiều cao (cm)',
  `weight` decimal(5,2) DEFAULT NULL COMMENT 'Cân nặng (kg)',
  `bmi` decimal(4,2) DEFAULT NULL COMMENT 'BMI tự động tính',
  `blood_pressure` varchar(20) DEFAULT NULL COMMENT 'Huyết áp (VD: 120/80)',
  `heart_rate` int(3) DEFAULT NULL COMMENT 'Nhịp tim (bpm)',
  `temperature` decimal(4,2) DEFAULT NULL COMMENT 'Nhiệt độ (°C)',
  `respiratory_rate` int(3) DEFAULT NULL COMMENT 'Nhịp thở (lần/phút)',
  `chief_complaint` text DEFAULT NULL COMMENT 'Lý do khám',
  `symptoms` text DEFAULT NULL COMMENT 'Triệu chứng',
  `diagnosis` text DEFAULT NULL COMMENT 'Chẩn đoán',
  `medical_history` text DEFAULT NULL COMMENT 'Tiền sử bệnh',
  `family_history` text DEFAULT NULL COMMENT 'Tiền sử gia đình',
  `allergies` text DEFAULT NULL COMMENT 'Dị ứng',
  `lab_results` text DEFAULT NULL COMMENT 'Kết quả xét nghiệm',
  `imaging_results` text DEFAULT NULL COMMENT 'Kết quả chẩn đoán hình ảnh',
  `treatment_plan` text DEFAULT NULL COMMENT 'Kế hoạch điều trị',
  `prescription` text DEFAULT NULL COMMENT 'Đơn thuốc',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú thêm',
  `follow_up_date` date DEFAULT NULL COMMENT 'Ngày tái khám',
  `status` enum('active','completed','archived') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL COMMENT 'ID bác sĩ tạo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `record_date`, `record_type`, `height`, `weight`, `bmi`, `blood_pressure`, `heart_rate`, `temperature`, `respiratory_rate`, `chief_complaint`, `symptoms`, `diagnosis`, `medical_history`, `family_history`, `allergies`, `lab_results`, `imaging_results`, `treatment_plan`, `prescription`, `notes`, `follow_up_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 4, 17, 4, '2026-01-10', 'checkup', 175.00, 70.00, 22.86, '120/80', 75, 36.50, 18, 'Khám sức khỏe định kỳ', 'Không có triệu chứng bất thường', 'Sức khỏe tốt, các chỉ số bình thường', 'Chưa có tiền sử bệnh lý', 'Gia đình khỏe mạnh', 'Không', 'Công thức máu bình thường', 'X-quang lồng ngực bình thường', 'Duy trì chế độ ăn uống và tập thể dục', 'Vitamin C 500mg, 1 viên/ngày', 'Bệnh nhân có ý thức bảo vệ sức khỏe tốt', NULL, 'completed', 1, '2026-01-15 14:37:31', '2026-01-15 14:37:31'),
(2, 1, 1, 1, '2026-01-15', 'consultation', 110.00, 20.00, 16.53, '100/60', 90, 38.50, 22, 'Sốt cao, ho nhiều', 'Ho đờm, sốt 39 độ, chảy mũi', 'Viêm phế quản phổi', 'Hay bị viêm họng', 'Không có', 'Không', 'Bạch cầu tăng cao', 'Phổi có đám mờ nhỏ', 'Kháng sinh, hạ sốt, long đờm', 'Augmentin, Hapacol, Bisolvon', 'Theo dõi nhiệt độ thường xuyên', '2026-01-20', 'active', 1, '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(3, 2, 3, 2, '2026-01-16', 'checkup', 158.00, 55.00, 22.03, '110/70', 80, 37.00, 20, 'Khám thai 8 tuần', 'Nghén nhẹ, mệt mỏi', 'Thai 8 tuần phát triển bình thường', 'Sinh thường 1 lần', 'Không', 'Không', 'Beta hCG bình thường', 'Siêu âm có tim thai', 'Bổ sung sắt, canxi, nghỉ ngơi', 'Ferrovit, Calcium Corbiere', 'Hẹn khám lại mốc 12 tuần', '2026-02-15', 'completed', 3, '2026-01-28 09:30:00', '2026-01-28 09:30:00'),
(4, 1, 3, NULL, '2026-02-02', 'consultation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '31', NULL, NULL, NULL, NULL, NULL, '', '', '31321', NULL, 'active', NULL, '2026-02-02 17:07:27', '2026-02-02 17:07:27'),
(5, 1, 2, NULL, '2026-02-02', 'consultation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', 'ốm', NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, 'active', NULL, '2026-02-02 17:12:19', '2026-02-02 17:12:19');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `dosage_form` varchar(100) NOT NULL,
  `strength` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `generic_name`, `category`, `dosage_form`, `strength`, `manufacturer`, `quantity`, `unit_price`, `expiry_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Panadol Extra', 'Paracetamol + Caffeine', 'Thuốc giảm đau', 'Viên nén', '500mg/65mg', 'Sandoz', 500, 1500.00, '2027-12-31', 'Giảm đau đầu, hạ sốt nhanh', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(2, 'Efferalgan', 'Paracetamol', 'Thuốc giảm đau', 'Viên sủi', '500mg', 'UPSA SAS', 300, 3000.00, '2027-06-30', 'Hạ sốt, giảm đau nhanh chóng dạng sủi', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(3, 'Augmentin', 'Amoxicillin + Clavulanic acid', 'Thuốc kháng sinh', 'Viên nén bao phim', '625mg', 'GlaxoSmithKline', 150, 18000.00, '2026-11-20', 'Kháng sinh điều trị nhiễm khuẩn đường hô hấp', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(4, 'Berberin', 'Berberine Chloride', 'Thuốc tiêu hóa', 'Viên nén', '10mg', 'Dược phẩm TW3', 1000, 500.00, '2028-05-15', 'Điều trị lỵ, tiêu chảy, đau bụng', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(5, 'Smecta', 'Diosmectite', 'Thuốc tiêu hóa', 'Gói bột', '3g', 'Ipsen', 400, 3500.00, '2027-08-10', 'Điều trị tiêu chảy cấp và mãn tính', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(6, 'Eugica Fort', 'Eucalyptol, Menthol...', 'Thuốc hô hấp', 'Viên nang mềm', '100mg', 'Mega We Care', 600, 1200.00, '2026-12-25', 'Trị ho, đau họng, sổ mũi, cảm cúm', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(7, 'Fugacar', 'Mebendazole', 'Thuốc tấy giun', 'Viên nén nhai', '500mg', 'Janssen', 200, 25000.00, '2028-02-14', 'Điều trị nhiễm các loại giun', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(8, 'Salonpas', 'Methyl Salicylate', 'Cao dán', 'Miếng dán', '6.5cm x 4.2cm', 'Hisamitsu', 1000, 2000.00, '2029-01-01', 'Giảm đau cơ, đau khớp, đau lưng', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(9, 'Nước muối sinh lý', 'Natri Clorid', 'Dung dịch sát khuẩn', 'Chai', '0.9% 500ml', 'Vĩnh Phúc', 500, 10000.00, '2027-09-09', 'Rửa vết thương, súc miệng', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(10, 'Vitamin C 500mg', 'Ascorbic Acid', 'Vitamin & Khoáng chất', 'Viên nang', '500mg', 'Dược Hậu Giang', 800, 800.00, '2027-03-20', 'Bổ sung Vitamin C tăng sức đề kháng', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(11, 'Hoạt huyết dưỡng não', 'Cao đinh lăng, cao bạch quả', 'Thực phẩm chức năng', 'Viên bao đường', 'N/A', 'Traphaco', 450, 2500.00, '2027-11-11', 'Tăng cường tuần hoàn máu não, giảm đau đầu', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00'),
(12, 'Strepsils Cool', '2,4-Dichlorobenzyl alcohol', 'Thuốc hô hấp', 'Viên ngậm', 'Viên', 'Reckitt Benckiser', 1000, 3000.00, '2028-06-15', 'Ngậm trị đau họng, the mát', 'admin', '2026-01-28 09:00:00', '2026-01-28 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_stock_log`
--

CREATE TABLE `medicine_stock_log` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action` varchar(50) DEFAULT 'update',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patreg`
--

CREATE TABLE `patreg` (
  `pid` int(11) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(30) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `cpassword` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL COMMENT 'A+, A-, B+, B-, O+, O-, AB+, AB-',
  `emergency_contact` varchar(10) DEFAULT NULL,
  `emergency_contact_name` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patreg`
--

INSERT INTO `patreg` (`pid`, `fname`, `lname`, `gender`, `email`, `contact`, `address`, `password`, `cpassword`, `avatar`, `date_of_birth`, `blood_group`, `emergency_contact`, `emergency_contact_name`, `updated_at`) VALUES
(1, 'An', 'Nguyễn Văn', 'Male', 'an.nguyen@email.com', '0912345678', '123 Kim Mã, Ba Đình, Hà Nội', '123', '123', NULL, '1990-05-15', 'A+', '0911223344', 'Nguyễn Thị Hoa', '2026-01-28 08:00:00'),
(2, 'Bình', 'Trần Thị', 'Female', 'binh.tran@email.com', '0987654321', '45 Nguyễn Trãi, Thanh Xuân, Hà Nội', '123', '123', NULL, '1985-08-20', 'O+', '0922334455', 'Trần Văn Cường', '2026-01-28 08:00:00'),
(3, 'Cường', 'Lê Mạnh', 'Male', 'cuong.le@email.com', '0901234567', '78 Cầu Giấy, Cầu Giấy, Hà Nội', '123', '123', NULL, '1995-12-10', 'B-', '0933445566', 'Lê Thị Mai', '2026-01-28 08:00:00'),
(4, 'Duy', 'Chu Quang', 'Male', 'duywinter@gmail.com', '0846181174', 'Hà Đông, Hà Nội', '123', '123', 'uploads/avatars/avatar_4_1768488136.jpg', '2000-01-01', 'AB+', '0988776655', 'Chu Văn Ba', '2026-01-15 14:42:16'),
(5, 'Hạnh', 'Phạm Thị', 'Female', 'hanh.pham@email.com', '0918273645', 'Số 5, Ngõ 10, Láng Hạ, Đống Đa, Hà Nội', '123', '123', NULL, '1992-03-25', 'O-', '0944556677', 'Phạm Văn Hùng', '2026-01-28 08:00:00'),
(6, 'Lan', 'Hoàng Ngọc', 'Female', 'lan.hoang@email.com', '0922334455', 'KĐT Times City, Hai Bà Trưng, Hà Nội', '123', '123', NULL, '1988-11-11', 'A-', '0955667788', 'Hoàng Tuấn', '2026-01-28 08:00:00'),
(7, 'Minh', 'Vũ Đức', 'Male', 'minh.vu@email.com', '0933445566', '102 Trần Phú, Hà Đông, Hà Nội', '123', '123', NULL, '1975-06-30', 'B+', '0966778899', 'Vũ Thị Loan', '2026-01-28 08:00:00'),
(8, 'Nhi', 'Đặng Uyên', 'Female', 'nhi.dang@email.com', '0944556677', '88 Lê Văn Lương, Thanh Xuân, Hà Nội', '123', '123', NULL, '1998-09-09', 'AB-', '0977889900', 'Đặng Văn Minh', '2026-01-28 08:00:00'),
(9, 'Quân', 'Trịnh Minh', 'Male', 'quan.trinh@email.com', '0955667788', 'P202, Chung cư B1, Mỹ Đình, Hà Nội', '123', '123', NULL, '2005-02-14', 'O+', '0988990011', 'Trịnh Thị Thu', '2026-01-28 08:00:00'),
(10, 'Tâm', 'Lý Thanh', 'Female', 'tam.ly@email.com', '0966778899', 'Số 1, Đại Cồ Việt, Hai Bà Trưng, Hà Nội', '123', '123', NULL, '1980-07-22', 'A+', '0999001122', 'Lý Văn Hải', '2026-01-28 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_medications`
--

CREATE TABLE `prescription_medications` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prestb`
--

CREATE TABLE `prestb` (
  `pres_id` int(11) NOT NULL,
  `doctor` varchar(50) NOT NULL,
  `pid` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `appdate` date NOT NULL,
  `apptime` time NOT NULL,
  `disease` varchar(250) NOT NULL,
  `allergy` varchar(250) NOT NULL,
  `prescription` varchar(1000) NOT NULL,
  `treatment_duration` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prestb`
--

INSERT INTO `prestb` (`pres_id`, `doctor`, `pid`, `ID`, `fname`, `lname`, `appdate`, `apptime`, `disease`, `allergy`, `prescription`, `treatment_duration`, `created_at`) VALUES
(1, 'Bùi Quốc Việt', 4, 6, 'Duy', 'Chu Quang', '2026-03-18', '13:30:00', 'Sốt virus', 'Không có', 'Paracetamol 500mg: 1 viên x 3 lần/ngày (sau ăn); Vitamin C: 2 viên/ngày', NULL, '2026-01-28 09:03:03'),
(2, 'Lê Minh Châu', 1, 101, 'An', 'Nguyễn Văn', '2026-02-14', '09:00:00', 'Viêm họng cấp', 'Penicillin', 'Augmentin 625mg: 1 viên x 2 lần/ngày (uống 7 ngày); Efferalgan: khi sốt > 38.5 độ', NULL, '2026-01-28 09:03:03'),
(3, 'Vũ Thị Giang', 2, 102, 'Bình', 'Trần Thị', '2026-02-15', '10:30:00', 'Khám thai định kỳ 12 tuần', 'Không có', 'Sắt và Axit Folic: 1 viên/ngày; Canxi: 1 viên/ngày (uống sáng)', NULL, '2026-01-28 09:03:03'),
(4, 'Hoàng Văn Em', 3, 103, 'Cường', 'Lê Mạnh', '2026-02-16', '14:00:00', 'Viêm da dị ứng', 'Hải sản', 'Fucicort kem bôi: 2 lần/ngày; Loratadin 10mg: 1 viên/tối', NULL, '2026-01-28 09:03:03'),
(5, 'Lý Văn Minh', 5, 104, 'Hạnh', 'Phạm Thị', '2026-02-17', '08:30:00', 'Rối loạn tiêu hóa', 'Không có', 'Berberin: 10 viên x 2 lần/ngày; Oresol: uống bù nước; Men vi sinh: 2 ống/ngày', NULL, '2026-01-28 09:03:03'),
(6, 'Phan Văn Sơn', 7, 105, 'Minh', 'Vũ Đức', '2026-02-18', '15:00:00', 'Đau lưng cấp', 'Không có', 'Mobic 7.5mg: 1 viên/ngày (sau ăn no); Myonal 50mg: 2 viên/ngày; Salonpas dán tại chỗ', NULL, '2026-01-28 09:03:03'),
(7, 'Bùi Văn Kiên', 10, 106, 'Tâm', 'Lý Thanh', '2026-02-19', '11:00:00', 'Đau đầu vận mạch', 'Không có', 'Hoạt huyết dưỡng não: 2 viên x 2 lần/ngày; Magne-B6: 2 viên/ngày; Nghỉ ngơi hợp lý', NULL, '2026-01-28 09:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `description`, `price`, `status`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'Khám tổng quát', 'Khám sức khỏe tổng quát đầy đủ', 500000.00, '1', '2026-02-02 13:05:21', '2026-02-02 13:05:21', 'System'),
(2, 'Siêu âm', 'Siêu âm chẩn đoán', 300000.00, '1', '2026-02-02 13:05:21', '2026-02-02 13:05:21', 'System'),
(3, 'Xét nghiệm máu', 'Xét nghiệm máu toàn bộ', 350000.00, '1', '2026-02-02 13:05:21', '2026-02-02 13:05:21', 'System'),
(4, 'Chụp X-quang', 'Chụp X-quang các bộ phận', 300000.00, '1', '2026-02-02 13:05:21', '2026-02-02 13:05:31', 'System'),
(5, 'Nhổ răng', 'Dịch vụ nhổ răng', 200000.00, '1', '2026-02-02 13:05:21', '2026-02-02 13:05:21', 'System');

-- --------------------------------------------------------

--
-- Table structure for table `service_ratings`
--

CREATE TABLE `service_ratings` (
  `id` int(11) NOT NULL,
  `spec_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_vi` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT 'fas fa-stethoscope',
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `average_rating` float DEFAULT 0,
  `total_ratings` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `name_vi`, `icon`, `description`, `status`, `created_at`, `average_rating`, `total_ratings`) VALUES
(1, 'Pediatrics', 'Nhi khoa', 'fas fa-baby', 'Khám và điều trị bệnh cho trẻ em từ sơ sinh đến 18 tuổi', 1, '2026-01-14 18:04:43', 0, 0),
(2, 'Obstetrics_Gynecology', 'Sản phụ khoa', 'fas fa-female', 'Chăm sóc sức khỏe phụ nữ, thai sản và sinh đẻ', 1, '2026-01-14 18:04:43', 0, 0),
(3, 'Dermatology', 'Da liễu', 'fas fa-allergies', 'Khám và điều trị các bệnh về da, tóc, móng', 1, '2026-01-14 18:04:43', 0, 0),
(4, 'Gastroenterology', 'Tiêu hóa', 'fas fa-pills', 'Khám và điều trị các bệnh về dạ dày, ruột, gan, mật', 1, '2026-01-14 18:04:43', 0, 0),
(5, 'Rheumatology', 'Cơ xương khớp', 'fas fa-bone', 'Khám và điều trị các bệnh về xương, khớp, cơ', 1, '2026-01-14 18:04:43', 0, 0),
(6, 'Allergy_Immunology', 'Dị ứng - Miễn dịch', 'fas fa-shield-virus', 'Khám và điều trị các bệnh dị ứng và hệ miễn dịch', 1, '2026-01-14 18:04:43', 0, 0),
(7, 'Anesthesiology', 'Gây mê hồi sức', 'fas fa-syringe', 'Chuyên khoa gây mê và hồi sức trong phẫu thuật', 1, '2026-01-14 18:04:43', 0, 0),
(8, 'ENT', 'Tai - Mũi - Họng', 'fas fa-head-side-cough', 'Khám và điều trị các bệnh tai, mũi, họng', 1, '2026-01-14 18:04:43', 0, 0),
(9, 'Oncology', 'Ung bướu', 'fas fa-ribbon', 'Chẩn đoán và điều trị các bệnh ung thư', 1, '2026-01-14 18:04:43', 0, 0),
(10, 'Cardiology', 'Tim mạch', 'fas fa-heartbeat', 'Khám và điều trị các bệnh về tim và mạch máu', 1, '2026-01-14 18:04:43', 0, 0),
(11, 'Geriatrics', 'Lão khoa', 'fas fa-user-clock', 'Chăm sóc sức khỏe người cao tuổi', 1, '2026-01-14 18:04:43', 0, 0),
(12, 'Orthopedics', 'Chấn thương chỉnh hình', 'fas fa-bone', 'Phẫu thuật và điều trị chấn thương xương khớp', 1, '2026-01-14 18:04:43', 0, 0),
(13, 'Emergency_Medicine', 'Hồi sức cấp cứu', 'fas fa-ambulance', 'Cấp cứu và hồi sức tích cực', 1, '2026-01-14 18:04:43', 0, 0),
(14, 'General_Surgery', 'Ngoại tổng quát', 'fas fa-cut', 'Phẫu thuật tổng quát các cơ quan', 1, '2026-01-14 18:04:43', 0, 0),
(15, 'Preventive_Medicine', 'Y học dự phòng', 'fas fa-shield-alt', 'Phòng ngừa bệnh tật và nâng cao sức khỏe', 1, '2026-01-14 18:04:43', 0, 0),
(16, 'Dentistry', 'Răng - Hàm - Mặt', 'fas fa-tooth', 'Khám và điều trị các bệnh về răng, hàm, mặt', 1, '2026-01-14 18:04:43', 0, 0),
(17, 'Infectious_Disease', 'Truyền nhiễm', 'fas fa-virus', 'Khám và điều trị các bệnh truyền nhiễm', 1, '2026-01-14 18:04:43', 0, 0),
(18, 'Nephrology', 'Nội thận', 'fas fa-prescription-bottle', 'Khám và điều trị các bệnh về thận', 1, '2026-01-14 18:04:43', 0, 0),
(19, 'Endocrinology', 'Nội tiết', 'fas fa-disease', 'Khám và điều trị các bệnh về nội tiết, tiểu đường', 1, '2026-01-14 18:04:43', 0, 0),
(20, 'Psychiatry', 'Tâm thần', 'fas fa-brain', 'Khám và điều trị các bệnh tâm thần', 1, '2026-01-14 18:04:43', 0, 0),
(21, 'Pulmonology', 'Hô hấp', 'fas fa-lungs', 'Khám và điều trị các bệnh về phổi và đường hô hấp', 1, '2026-01-14 18:04:43', 0, 0),
(22, 'Laboratory', 'Xét nghiệm', 'fas fa-vials', 'Xét nghiệm máu, nước tiểu và các chỉ số', 1, '2026-01-14 18:04:43', 0, 0),
(23, 'Hematology', 'Huyết học', 'fas fa-tint', 'Khám và điều trị các bệnh về máu', 1, '2026-01-14 18:04:43', 0, 0),
(24, 'Psychology', 'Tâm lý', 'fas fa-comments', 'Tư vấn và trị liệu tâm lý', 1, '2026-01-14 18:04:43', 0, 0),
(25, 'Neurology', 'Nội thần kinh', 'fas fa-brain', 'Khám và điều trị các bệnh về thần kinh', 1, '2026-01-14 18:04:43', 0, 0),
(26, 'Speech_Therapy', 'Ngôn ngữ trị liệu', 'fas fa-comment-medical', 'Điều trị các rối loạn ngôn ngữ và giao tiếp', 1, '2026-01-14 18:04:43', 0, 0),
(27, 'Rehabilitation', 'Phục hồi chức năng - VLTL', 'fas fa-walking', 'Phục hồi chức năng và vật lý trị liệu', 1, '2026-01-14 18:04:43', 0, 0),
(28, 'Fertility', 'Vô sinh hiếm muộn', 'fas fa-baby-carriage', 'Điều trị vô sinh và hỗ trợ sinh sản', 1, '2026-01-14 18:04:43', 0, 0),
(29, 'Traditional_Medicine', 'Y học cổ truyền', 'fas fa-leaf', 'Khám và điều trị bằng y học cổ truyền', 1, '2026-01-14 18:04:43', 0, 0),
(30, 'Tuberculosis', 'Lao - Bệnh phổi', 'fas fa-lungs-virus', 'Khám và điều trị bệnh lao và các bệnh phổi', 1, '2026-01-14 18:04:43', 0, 0),
(31, 'Sports_Medicine', 'Y học thể thao', 'fas fa-running', 'Chăm sóc sức khỏe cho vận động viên', 1, '2026-01-14 18:04:43', 0, 0),
(32, 'Ophthalmology', 'Nhãn khoa', 'fas fa-eye', 'Khám và điều trị các bệnh về mắt', 1, '2026-01-14 18:04:43', 0, 0),
(33, 'Andrology', 'Nam khoa', 'fas fa-male', 'Khám và điều trị các bệnh nam giới', 1, '2026-01-14 18:04:43', 0, 0),
(34, 'Urology', 'Ngoại tiết niệu', 'fas fa-procedures', 'Phẫu thuật và điều trị bệnh tiết niệu', 1, '2026-01-14 18:04:43', 0, 0),
(35, 'Radiology', 'Chẩn đoán hình ảnh', 'fas fa-x-ray', 'Chụp X-quang, CT, MRI và siêu âm', 1, '2026-01-14 18:04:43', 0, 0),
(36, 'Neurosurgery', 'Ngoại thần kinh', 'fas fa-brain', 'Phẫu thuật thần kinh và não', 1, '2026-01-14 18:04:43', 0, 0),
(37, 'Internal_Medicine', 'Nội tổng quát', 'fas fa-stethoscope', 'Khám và điều trị bệnh nội khoa tổng quát', 1, '2026-01-14 18:04:43', 0, 0),
(38, 'Urology_Internal', 'Ngoại niệu', 'fas fa-procedures', 'Khám và điều trị bệnh đường tiết niệu', 1, '2026-01-14 18:04:43', 0, 0),
(39, 'Nutrition', 'Dinh dưỡng', 'fas fa-apple-alt', 'Tư vấn dinh dưỡng và chế độ ăn', 1, '2026-01-14 18:04:43', 0, 0),
(40, 'Thoracic_Surgery', 'Ngoại lồng ngực - Mạch máu', 'fas fa-heart', 'Phẫu thuật lồng ngực và mạch máu', 1, '2026-01-14 18:04:43', 0, 0),
(41, 'Plastic_Surgery', 'Phẫu thuật tạo hình (Thẩm mỹ)', 'fas fa-magic', 'Phẫu thuật thẩm mỹ và tạo hình', 1, '2026-01-14 18:04:43', 0, 0),
(42, 'Pain_Management', 'Điều trị đau', 'fas fa-band-aid', 'Điều trị và quản lý đau mãn tính', 1, '2026-01-14 18:04:43', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `status` enum('available','booked','blocked') DEFAULT 'available',
  `appointment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `doctor_id`, `slot_date`, `slot_time`, `status`, `appointment_id`, `created_at`) VALUES
(1, 17, '2026-02-26', '08:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(2, 17, '2026-02-26', '08:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(3, 17, '2026-02-26', '09:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(4, 17, '2026-02-26', '09:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(5, 17, '2026-02-26', '10:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(6, 17, '2026-02-26', '10:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(7, 17, '2026-02-26', '11:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(8, 17, '2026-02-26', '11:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(9, 17, '2026-02-26', '12:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(10, 17, '2026-02-26', '12:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(11, 17, '2026-02-26', '13:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(12, 17, '2026-02-26', '13:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(13, 17, '2026-02-26', '14:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(14, 17, '2026-02-26', '14:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(15, 17, '2026-02-26', '15:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(16, 17, '2026-02-26', '15:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(17, 17, '2026-02-26', '16:00:00', 'available', NULL, '2026-01-15 14:11:32'),
(18, 17, '2026-02-26', '16:30:00', 'available', NULL, '2026-01-15 14:11:32'),
(19, 9, '2026-11-18', '08:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(20, 9, '2026-11-18', '08:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(21, 9, '2026-11-18', '09:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(22, 9, '2026-11-18', '09:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(23, 9, '2026-11-18', '10:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(24, 9, '2026-11-18', '10:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(25, 9, '2026-11-18', '11:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(26, 9, '2026-11-18', '11:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(27, 9, '2026-11-18', '12:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(28, 9, '2026-11-18', '12:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(29, 9, '2026-11-18', '13:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(30, 9, '2026-11-18', '13:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(31, 9, '2026-11-18', '14:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(32, 9, '2026-11-18', '14:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(33, 9, '2026-11-18', '15:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(34, 9, '2026-11-18', '15:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(35, 9, '2026-11-18', '16:00:00', 'available', NULL, '2026-01-15 14:12:06'),
(36, 9, '2026-11-18', '16:30:00', 'available', NULL, '2026-01-15 14:12:06'),
(37, 17, '2026-03-18', '08:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(38, 17, '2026-03-18', '08:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(39, 17, '2026-03-18', '09:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(40, 17, '2026-03-18', '09:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(41, 17, '2026-03-18', '10:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(42, 17, '2026-03-18', '10:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(43, 17, '2026-03-18', '11:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(44, 17, '2026-03-18', '11:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(45, 17, '2026-03-18', '12:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(46, 17, '2026-03-18', '12:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(47, 17, '2026-03-18', '13:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(48, 17, '2026-03-18', '13:30:00', 'booked', 6, '2026-01-15 14:19:46'),
(49, 17, '2026-03-18', '14:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(50, 17, '2026-03-18', '14:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(51, 17, '2026-03-18', '15:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(52, 17, '2026-03-18', '15:30:00', 'available', NULL, '2026-01-15 14:19:46'),
(53, 17, '2026-03-18', '16:00:00', 'available', NULL, '2026-01-15 14:19:46'),
(54, 17, '2026-03-18', '16:30:00', 'available', NULL, '2026-01-15 14:19:46');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_records`
--

CREATE TABLE `vaccination_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `vaccine_type` varchar(100) DEFAULT NULL,
  `dose_number` int(2) DEFAULT 1,
  `vaccination_date` date NOT NULL,
  `next_dose_date` date DEFAULT NULL,
  `administered_by` int(11) DEFAULT NULL COMMENT 'ID bác sĩ',
  `location` varchar(100) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_doctors`
-- (See below for the actual view)
--
CREATE TABLE `v_doctors` (
`id` int(11)
,`username` varchar(255)
,`fullname` varchar(255)
,`email` varchar(255)
,`spec` varchar(255)
,`spec_id` int(11)
,`spec_name_vi` varchar(100)
,`spec_icon` varchar(100)
,`docFees` int(10)
,`phone` varchar(15)
,`bio` mediumtext
,`experience_years` int(3)
,`status` tinyint(1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_medical_records_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_medical_records_summary` (
`id` int(11)
,`patient_id` int(11)
,`patient_name` varchar(41)
,`patient_contact` varchar(10)
,`blood_group` varchar(5)
,`doctor_id` int(11)
,`doctor_name` varchar(255)
,`record_date` date
,`record_type` enum('consultation','checkup','emergency','followup','surgery')
,`diagnosis` text
,`status` enum('active','completed','archived')
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_patient_profiles`
-- (See below for the actual view)
--
CREATE TABLE `v_patient_profiles` (
`pid` int(11)
,`fname` varchar(20)
,`lname` varchar(20)
,`gender` varchar(10)
,`email` varchar(30)
,`contact` varchar(10)
,`address` varchar(255)
,`avatar` varchar(255)
,`date_of_birth` date
,`blood_group` varchar(5)
,`emergency_contact` varchar(10)
,`emergency_contact_name` varchar(50)
,`age` bigint(21)
,`total_appointments` bigint(21)
,`total_records` bigint(21)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointmenttb`
--
ALTER TABLE `appointmenttb`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `doctb`
--
ALTER TABLE `doctb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_ratings`
--
ALTER TABLE `doctor_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`doctor_id`,`patient_id`,`appointment_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `forum_attachments`
--
ALTER TABLE `forum_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`user_type`,`target_id`,`target_type`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_id` (`target_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_type` (`user_type`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `medical_attachments`
--
ALTER TABLE `medical_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `medical_documents`
--
ALTER TABLE `medical_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `doctor` (`doctor`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `record_date` (`record_date`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Indexes for table `medicine_stock_log`
--
ALTER TABLE `medicine_stock_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `patreg`
--
ALTER TABLE `patreg`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `prescription_medications`
--
ALTER TABLE `prescription_medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`);

--
-- Indexes for table `prestb`
--
ALTER TABLE `prestb`
  ADD PRIMARY KEY (`pres_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_name` (`service_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `service_ratings`
--
ALTER TABLE `service_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spec_id` (`spec_id`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`doctor_id`,`slot_date`,`slot_time`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `slot_date` (`slot_date`);

--
-- Indexes for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointmenttb`
--
ALTER TABLE `appointmenttb`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctb`
--
ALTER TABLE `doctb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `doctor_ratings`
--
ALTER TABLE `doctor_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `forum_attachments`
--
ALTER TABLE `forum_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_likes`
--
ALTER TABLE `forum_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medical_attachments`
--
ALTER TABLE `medical_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_documents`
--
ALTER TABLE `medical_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `medicine_stock_log`
--
ALTER TABLE `medicine_stock_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patreg`
--
ALTER TABLE `patreg`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `prescription_medications`
--
ALTER TABLE `prescription_medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestb`
--
ALTER TABLE `prestb`
  MODIFY `pres_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_ratings`
--
ALTER TABLE `service_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `v_doctors`
--
DROP TABLE IF EXISTS `v_doctors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_doctors`  AS SELECT `d`.`id` AS `id`, `d`.`username` AS `username`, `d`.`fullname` AS `fullname`, `d`.`email` AS `email`, `d`.`spec` AS `spec`, `d`.`spec_id` AS `spec_id`, `s`.`name_vi` AS `spec_name_vi`, `s`.`icon` AS `spec_icon`, `d`.`docFees` AS `docFees`, `d`.`phone` AS `phone`, `d`.`bio` AS `bio`, `d`.`experience_years` AS `experience_years`, `d`.`status` AS `status` FROM (`doctb` `d` left join `specializations` `s` on(`d`.`spec_id` = `s`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_medical_records_summary`
--
DROP TABLE IF EXISTS `v_medical_records_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_medical_records_summary`  AS SELECT `mr`.`id` AS `id`, `mr`.`patient_id` AS `patient_id`, concat(`p`.`fname`,' ',`p`.`lname`) AS `patient_name`, `p`.`contact` AS `patient_contact`, `p`.`blood_group` AS `blood_group`, `mr`.`doctor_id` AS `doctor_id`, `d`.`fullname` AS `doctor_name`, `mr`.`record_date` AS `record_date`, `mr`.`record_type` AS `record_type`, `mr`.`diagnosis` AS `diagnosis`, `mr`.`status` AS `status`, `mr`.`created_at` AS `created_at` FROM ((`medical_records` `mr` left join `patreg` `p` on(`mr`.`patient_id` = `p`.`pid`)) left join `doctb` `d` on(`mr`.`doctor_id` = `d`.`id`)) ORDER BY `mr`.`record_date` DESC, `mr`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_patient_profiles`
--
DROP TABLE IF EXISTS `v_patient_profiles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_patient_profiles`  AS SELECT `p`.`pid` AS `pid`, `p`.`fname` AS `fname`, `p`.`lname` AS `lname`, `p`.`gender` AS `gender`, `p`.`email` AS `email`, `p`.`contact` AS `contact`, `p`.`address` AS `address`, `p`.`avatar` AS `avatar`, `p`.`date_of_birth` AS `date_of_birth`, `p`.`blood_group` AS `blood_group`, `p`.`emergency_contact` AS `emergency_contact`, `p`.`emergency_contact_name` AS `emergency_contact_name`, timestampdiff(YEAR,`p`.`date_of_birth`,curdate()) AS `age`, count(distinct `a`.`ID`) AS `total_appointments`, count(distinct `mr`.`id`) AS `total_records` FROM ((`patreg` `p` left join `appointmenttb` `a` on(`p`.`pid` = `a`.`pid`)) left join `medical_records` `mr` on(`p`.`pid` = `mr`.`patient_id`)) GROUP BY `p`.`pid` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `doctor_ratings`
--
ALTER TABLE `doctor_ratings`
  ADD CONSTRAINT `doctor_ratings_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctb` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_ratings_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patreg` (`pid`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_ratings_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointmenttb` (`ID`) ON DELETE SET NULL;

--
-- Constraints for table `forum_attachments`
--
ALTER TABLE `forum_attachments`
  ADD CONSTRAINT `forum_attachments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `forum_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_stock_log`
--
ALTER TABLE `medicine_stock_log`
  ADD CONSTRAINT `medicine_stock_log_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_medications`
--
ALTER TABLE `prescription_medications`
  ADD CONSTRAINT `fk_prestb_medications` FOREIGN KEY (`prescription_id`) REFERENCES `prestb` (`pres_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_ratings`
--
ALTER TABLE `service_ratings`
  ADD CONSTRAINT `service_ratings_ibfk_1` FOREIGN KEY (`spec_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
