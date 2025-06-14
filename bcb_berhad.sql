-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 08:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bcb_berhad`
--
CREATE DATABASE IF NOT EXISTS `bcb_berhad` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bcb_berhad`;

-- --------------------------------------------------------

--
-- Table structure for table `annual_leave`
--

DROP TABLE IF EXISTS `annual_leave`;
CREATE TABLE IF NOT EXISTS `annual_leave` (
  `emp_id` varchar(20) NOT NULL,
  `leave_date` date NOT NULL,
  PRIMARY KEY (`emp_id`,`leave_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annual_leave`
--

INSERT INTO `annual_leave` (`emp_id`, `leave_date`) VALUES
('2222', '2025-05-01'),
('4646', '2025-04-28'),
('4646', '2025-04-29'),
('777', '2025-05-04'),
('777', '2025-05-12');

-- --------------------------------------------------------

--
-- Table structure for table `daily_summary`
--

DROP TABLE IF EXISTS `daily_summary`;
CREATE TABLE IF NOT EXISTS `daily_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(100) NOT NULL,
  `first_clock_in` time DEFAULT NULL,
  `last_clock_out` time DEFAULT NULL,
  `attendance_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_id` (`emp_id`,`attendance_date`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_summary`
--

INSERT INTO `daily_summary` (`id`, `emp_id`, `first_clock_in`, `last_clock_out`, `attendance_date`) VALUES
(131, '777', '17:25:11', '18:01:43', '2025-06-09'),
(133, '777', '18:14:31', '18:17:15', '2025-06-12');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`, `status`) VALUES
(12, 'Human Resources', '2025-01-13 08:06:52', 1),
(27, 'Marketing', '2025-01-13 08:07:39', 1),
(55, 'sales', '2025-01-16 07:07:37', 0),
(88, 'IT', '2025-01-13 08:07:07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `face_recognition`
--

DROP TABLE IF EXISTS `face_recognition`;
CREATE TABLE IF NOT EXISTS `face_recognition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(100) NOT NULL,
  `action` varchar(20) NOT NULL,
  `time` time NOT NULL,
  `attendance_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `face_recognition`
--

INSERT INTO `face_recognition` (`id`, `emp_id`, `action`, `time`, `attendance_date`) VALUES
(104, '777', 'clock_in', '17:25:11', '2025-06-09'),
(105, '777', 'clock_out', '18:01:43', '2025-06-09'),
(106, '777', 'clock_in', '18:14:31', '2025-06-12'),
(107, '777', 'clock_in', '18:14:48', '2025-06-12'),
(108, '777', 'clock_in', '18:14:55', '2025-06-12'),
(109, '777', 'clock_in', '18:15:21', '2025-06-12'),
(110, '777', 'clock_in', '18:16:21', '2025-06-12'),
(111, '777', 'clock_out', '18:17:15', '2025-06-12');

--
-- Triggers `face_recognition`
--
DROP TRIGGER IF EXISTS `after_face_recognition_insert`;
DELIMITER $$
CREATE TRIGGER `after_face_recognition_insert` AFTER INSERT ON `face_recognition` FOR EACH ROW BEGIN
    IF NEW.action = 'clock_in' THEN
        INSERT INTO daily_summary (emp_id, first_clock_in, attendance_date)
        VALUES (NEW.emp_id, NEW.time, NEW.attendance_date)
        ON DUPLICATE KEY UPDATE 
            first_clock_in = LEAST(first_clock_in, NEW.time);
        
    ELSEIF NEW.action = 'clock_out' THEN
        INSERT INTO daily_summary (emp_id, last_clock_out, attendance_date)
        VALUES (NEW.emp_id, NEW.time, NEW.attendance_date)
        ON DUPLICATE KEY UPDATE 
            last_clock_out = GREATEST(IFNULL(last_clock_out, '00:00:00'), NEW.time);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `manual_login`
--

DROP TABLE IF EXISTS `manual_login`;
CREATE TABLE IF NOT EXISTS `manual_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(20) DEFAULT 'none',
  `clock` varchar(10) DEFAULT 'none',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manual_login`
--

INSERT INTO `manual_login` (`id`, `emp_id`, `image`, `date`, `time`, `status`, `clock`) VALUES
(105, 777, NULL, '2025-06-09', '17:25:11', 'Approved', 'clockIn'),
(106, 777, NULL, '2025-06-09', '18:01:43', 'Approved', 'clockOut'),
(107, 9494, NULL, '2025-06-09', '20:48:23', 'none', 'none');

-- --------------------------------------------------------

--
-- Table structure for table `public_holiday`
--

DROP TABLE IF EXISTS `public_holiday`;
CREATE TABLE IF NOT EXISTS `public_holiday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_date` date NOT NULL,
  `holiday_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_holiday`
--

INSERT INTO `public_holiday` (`id`, `holiday_date`, `holiday_name`) VALUES
(53, '2025-03-31', 'Raya'),
(60, '2025-03-25', 'public'),
(67, '2025-04-22', 'holiday');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Manager'),
(3, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `deactivation_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role_id` int(11) DEFAULT NULL,
  `default_password` tinyint(1) DEFAULT 1,
  `session_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`emp_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_department` (`department_id`),
  KEY `fk_role` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98563 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`emp_id`, `name`, `email`, `password`, `department_id`, `profile_picture`, `created_at`, `image`, `status`, `deactivation_date`, `updated_at`, `role_id`, `default_password`, `session_token`) VALUES
(777, 'Nur Ain Muharam', 'ain.na308@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bVpoc3FiV0tyd0pDU0w2bA$KXJNCNp4a9W0ntu2LY8zc0s+EIy0eQ0pFox2fxiudHE', 12, 'employee_picture/777.png', '2025-04-09', NULL, 1, NULL, '2025-05-14 13:07:28', 1, 0, '45f8c4d255f6c9db0c8422c2b03e1f2b69813fbe07339b0728813fece93babdd'),
(1818, 'Fatin Aqilah ', 'fatinaqilah818@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$azMvUVA4VThqYXUxTHhocA$ERG/eTw70sWl4KSldhDc/cm8Z9GAK+mM4Xyc6iyL4gk', 27, 'employee_picture/1818.png', '2025-05-26', NULL, 0, NULL, '2025-05-26 14:48:48', 2, 1, NULL),
(2222, 'zazaza', 'zaza@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$WEVMWllpODFvUDdGOHkvSw$L2XFtAp+Jb4jPLYLBQzWLqlBi7N1WNkezWUXA5xATag', 27, 'employee_picture/2222.png', '2025-05-14', NULL, 0, '2025-06-09', '2025-05-17 15:24:44', 3, 1, NULL),
(2626, 'adriana husna', 'yanahusna46@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$OUlmbWNTSzMvUnlJTFhDWQ$+tSCkxRkgCBCSqp1c/t8uRzJpS5De4IBplK1wVkRqFE', 88, 'employee_picture/2626.png', '2025-04-17', NULL, 1, NULL, '2025-05-15 19:26:22', 2, 0, '669c78a99a378a70d978e912feaf5ff784e3e4ed617b84d28f7728eb449554a6'),
(6565, 'popo', 'popo@gmail.com', '$2y$10$XfXI4b4CBP3gox1sbV3jb.FBwksgyWjdJAq8RvFY.A4I0BMTJXvNS', 88, 'employee_picture/6565.png', '2025-05-30', NULL, 0, '2025-06-09', '2025-05-30 05:41:20', 3, 1, '5978422f347cad7e9398a45ee4bf546d99ea1f0cc12a23f604b92b3f14b8d574'),
(9494, 'Raziq Hanan', 'raziq@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$Y1dFMTJMWjRrazdCVUhGVw$AHuJ86S+9EHBBR/44MTV7rw4SUfH0z2dB4TbqjChiDw', 27, 'employee_picture/9494.png', '2025-05-25', NULL, 1, NULL, '2025-05-25 08:32:27', 3, 0, 'b30715b2adf3590dbf724887b6f0c1ac97e3415652dcc966c5de6631eb3e9aa5'),
(98562, 'iki', 'iki@gmail.com', '$2y$10$hN2KiyQSDKDUiAeU7mQ6ue4KfVS05N.Lz75JXXc0rQV5twfST51vm', 12, 'employee_picture/98562.png', '2025-06-09', NULL, 0, '2025-06-09', '2025-06-09 05:33:48', 2, 1, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
