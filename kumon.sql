-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 09:24 PM
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
-- Database: `kumon`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `ic_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `subject_assigned` enum('Mathematics','English') NOT NULL,
  `date_of_birth` date NOT NULL,
  `language` enum('English','Malay') NOT NULL,
  `contact_no` int(11) NOT NULL,
  `position` enum('Admin','Teacher') NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_ID`, `name`, `age`, `gender`, `ic_no`, `email`, `address`, `subject_assigned`, `date_of_birth`, `language`, `contact_no`, `position`, `password`) VALUES
(1, 'admin', 25, 'Male', '111111111111', 'admin@gmail.com', 'Kumon', 'English', '2004-05-12', 'Malay', 1111111111, 'Admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `classwork`
--

CREATE TABLE `classwork` (
  `classwork_ID` int(11) NOT NULL,
  `subject` enum('Mathematics','English') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classwork`
--

INSERT INTO `classwork` (`classwork_ID`, `subject`) VALUES
(1, 'Mathematics'),
(2, 'English');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `name` varchar(50) NOT NULL,
  `relationship_with_student` varchar(50) NOT NULL,
  `ic_no` varchar(12) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `contact_no` varchar(11) NOT NULL,
  `parents_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`name`, `relationship_with_student`, `ic_no`, `email`, `password`, `contact_no`, `parents_ID`) VALUES
('Fatin Aqilah Binti Mohd Puad', 'Mother', '020818011266', 'fatinaqilah@gmail.com', 'password', '0136275321', 1),
('Abu Bin Samad', 'Father', '030818011266', 'xxx@xxx.xx', '@Password1234', '01111111111', 22);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `class_enrollment` enum('Mathematics','English','Mathematics & English') NOT NULL,
  `date_of_birth` date NOT NULL,
  `days` enum('Monday & Tuesday','Monday & Thursday','Monday & Friday','Tuesday & Thursday','Tuesday & Friday','Thursday & Friday') NOT NULL,
  `time` enum('2:00 - 3:00 p.m.','3:00 - 4:00 p.m.','4:00 - 5:00 p.m.','5:00 - 6:00 p.m.','6:00 - 7:00 p.m.','7:00 - 8:00 p.m.','8:00 - 9:00 p.m.','9:00 - 10:00 p.m.') NOT NULL,
  `rank` enum('A','B','C','D','E','F') DEFAULT NULL,
  `created_at` date DEFAULT curdate(),
  `parent_ic_no` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_ID`, `name`, `age`, `gender`, `class_enrollment`, `date_of_birth`, `days`, `time`, `rank`, `created_at`, `parent_ic_no`) VALUES
(28, 'ALI BIN ABU', 5, 'Male', 'Mathematics & English', '2024-12-05', 'Monday & Tuesday', '6:00 - 7:00 p.m.', 'C', '2025-04-03', '020818011266'),
(46, 'Abu Bin Samad', 8, 'Female', 'English', '2025-05-13', 'Monday & Friday', '6:00 - 7:00 p.m.', NULL, '2025-05-01', '020818011266'),
(47, 'Melur', 6, 'Female', 'Mathematics', '2025-05-22', 'Tuesday & Thursday', '6:00 - 7:00 p.m.', NULL, '2025-05-01', '020818011266'),
(48, 'Ali Bin Abu', 7, 'Male', 'Mathematics', '2025-05-14', 'Monday & Thursday', '2:00 - 3:00 p.m.', NULL, '2025-05-02', '030818011266'),
(49, 'Nur Melur', 8, 'Female', 'English', '2025-05-11', 'Tuesday & Friday', '6:00 - 7:00 p.m.', NULL, '2025-05-02', '030818011266');

-- --------------------------------------------------------

--
-- Table structure for table `student_classwork`
--

CREATE TABLE `student_classwork` (
  `student_classwork_ID` int(11) NOT NULL,
  `month` enum('January','February','March','April','May','June','July','August','September','October','November','December') NOT NULL,
  `year` enum('2023','2024','2025') NOT NULL,
  `classwork` varchar(10) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `level` varchar(10) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `score` varchar(10) DEFAULT NULL,
  `attendance` tinyint(1) NOT NULL,
  `submission` tinyint(1) NOT NULL,
  `student_ID` int(11) NOT NULL,
  `classwork_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classwork`
--

INSERT INTO `student_classwork` (`student_classwork_ID`, `month`, `year`, `classwork`, `date`, `level`, `number`, `time`, `score`, `attendance`, `submission`, `student_ID`, `classwork_ID`) VALUES
(1, 'April', '2024', 'G', 12, 'G', 66, 32, '6', 1, 1, 28, 2),
(2, 'May', '2024', 'B', 5, 'B', 11, 23, '6', 0, 0, 28, 1),
(3, 'June', '2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 28, 1),
(4, '', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 28, 1),
(5, 'March', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 28, 1),
(6, 'March', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 28, 1),
(7, 'April', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 28, 1),
(8, 'January', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 28, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_test`
--

CREATE TABLE `student_test` (
  `student_test_ID` int(11) NOT NULL,
  `level` varchar(10) DEFAULT NULL,
  `month` enum('January','February','March','April','May','June','July','August','September','October','November','December') DEFAULT NULL,
  `year` enum('2023','2024','2025') DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `grade` varchar(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `student_ID` int(11) DEFAULT NULL,
  `test_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_test`
--

INSERT INTO `student_test` (`student_test_ID`, `level`, `month`, `year`, `date`, `grade`, `time`, `status`, `student_ID`, `test_ID`) VALUES
(1, 'A', 'January', '2023', 12, '2', 30, 'Pass!', 28, 1),
(2, 'B', 'April', '2024', 13, '2', 32, 'Good', 28, 1),
(3, 'B', 'June', '2024', 6, '2', 32, 'Good', 28, 2),
(4, 'D', 'September', '2024', 44, '4', 4, 'f', 28, 2);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `ic_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `subject_assigned` enum('Mathematics','English') NOT NULL,
  `date_of_birth` date NOT NULL,
  `contact_no` int(11) NOT NULL,
  `password` varchar(50) NOT NULL,
  `classwork_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_ID`, `name`, `age`, `gender`, `ic_no`, `email`, `subject_assigned`, `date_of_birth`, `contact_no`, `password`, `classwork_ID`) VALUES
(1, 'ZABEDAH BINTI ABDULLAH', 25, 'Female', '111111111111', 'teacher@gmail.com', 'English', '2024-12-05', 1111111111, 'teacher', 1),
(2, 'AHMAD BIN SAMAD', 25, 'Male', '0102818011266', 'teacher1@gmail.com', 'English', '2016-01-13', 136275321, 'teacher', 1);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `test_ID` int(11) NOT NULL,
  `subject` enum('Mathematics','English') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`test_ID`, `subject`) VALUES
(1, 'Mathematics'),
(2, 'English');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_ID`),
  ADD UNIQUE KEY `ic_no` (`ic_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `classwork`
--
ALTER TABLE `classwork`
  ADD PRIMARY KEY (`classwork_ID`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`ic_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `contact_no` (`contact_no`),
  ADD UNIQUE KEY `parents_ID` (`parents_ID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_ID`),
  ADD KEY `fk_student_parent` (`parent_ic_no`);

--
-- Indexes for table `student_classwork`
--
ALTER TABLE `student_classwork`
  ADD PRIMARY KEY (`student_classwork_ID`),
  ADD KEY `student_ID` (`student_ID`),
  ADD KEY `fk_classwork` (`classwork_ID`);

--
-- Indexes for table `student_test`
--
ALTER TABLE `student_test`
  ADD PRIMARY KEY (`student_test_ID`),
  ADD KEY `student_ID` (`student_ID`),
  ADD KEY `fk_student_test_test` (`test_ID`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_ID`),
  ADD UNIQUE KEY `ic_no` (`ic_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact_no` (`contact_no`),
  ADD KEY `fk_classwork_ID` (`classwork_ID`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`test_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `classwork`
--
ALTER TABLE `classwork`
  MODIFY `classwork_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parents_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `student_classwork`
--
ALTER TABLE `student_classwork`
  MODIFY `student_classwork_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_test`
--
ALTER TABLE `student_test`
  MODIFY `student_test_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `test_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_parent` FOREIGN KEY (`parent_ic_no`) REFERENCES `parents` (`ic_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_classwork`
--
ALTER TABLE `student_classwork`
  ADD CONSTRAINT `fk_classwork` FOREIGN KEY (`classwork_ID`) REFERENCES `classwork` (`classwork_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_ID`) REFERENCES `student` (`student_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_test`
--
ALTER TABLE `student_test`
  ADD CONSTRAINT `fk_student_test_test` FOREIGN KEY (`test_ID`) REFERENCES `test` (`test_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_test_ibfk_1` FOREIGN KEY (`student_ID`) REFERENCES `student` (`student_ID`);

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_classwork_ID` FOREIGN KEY (`classwork_ID`) REFERENCES `classwork` (`classwork_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
