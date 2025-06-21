-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 21, 2025 at 01:25 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '000');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `class_number` int(11) DEFAULT NULL,
  `enrollment_open` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` varchar(6) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `units` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `status`, `units`) VALUES
('AR100', 'اللغة العربية 1', 'open', 2),
('AR101', 'اللغة العربية 2', 'open', 2),
('AR213', 'اللغة العربية 3', 'open', 2),
('AR216', 'اللغة العربية 4', 'open', 2),
('CS100', 'حاسب آلي 1', 'open', 2),
('CS101', 'حاسب آلي 2', 'open', 2),
('CS110', 'مقدمة الى الحاسب', 'open', 3),
('CS111', 'أساسيات برمجة', 'open', 3),
('CS112', 'تطبيقات الحاسب الشخصي', 'closed', 3),
('CS211', 'البرمجة بلغة الباسكال', 'closed', 3),
('CS212', 'طرق عددية', 'closed', 3),
('CS213', 'تنظيم حاسبات', 'closed', 3),
('CS214', '2فيجول بيسك', 'closed', 3),
('CS215', 'مبادئ صيانة الحاسب', 'closed', 1),
('CS216', 'البرمجة بلغة c', 'closed', 4),
('CS217', 'قواعد بيانات', 'closed', 4),
('CS310', 'نظم تشغيل', 'closed', 3),
('CS311', 'البرمجة الشيئية (+C)', 'closed', 3),
('CS312', 'تراكيب البيانات 1', 'closed', 4),
('CS313', '1فيجول بيسك', 'closed', 3),
('CS314', 'تحليل النظم', 'closed', 3),
('CS315', 'تقنية المعلومات', 'closed', 3),
('CS316', 'طرق تدريس الحاسوب', 'closed', 2),
('CS317', 'تراكيب بيانات 2', 'closed', 4),
('CS318', 'مبادئ برمجة الانترنت', 'closed', 3),
('CS325E', 'هندسة برمجيات', 'closed', 3),
('CS326E', 'حلقة نقاش', 'closed', 3),
('CS412', 'الرسم بالحاسب', 'closed', 3),
('CS413', 'الذكاء الاصطناعي', 'closed', 3),
('CS422E', 'لغة البرمجة', 'closed', 3),
('CS423E', 'برمجة انترنت متقدمة', 'closed', 3),
('CS424E', 'لغة جافا', 'closed', 3),
('CS426E', 'ادارة المشاريع', 'closed', 3),
('CS427E', 'وسائط متعددة', 'closed', 3),
('CS428', 'المشروع', 'closed', 2),
('EL100', 'اللغة الانجليزية 1', 'closed', 2),
('EL101', 'اللغة الانجليزية 2', 'closed', 2),
('GS100', 'علم النفس العام', 'closed', 2),
('GS101', 'أصول التربية', 'closed', 2),
('GS200', 'علم النفس الارتقائ', 'closed', 2),
('GS201', 'طرق التدريس العامة', 'closed', 2),
('GS202', 'أسس المناهج', 'closed', 2),
('GS203', 'علم النفس التربوي', 'closed', 2),
('GS301', 'طرق البحث التربوي', 'closed', 2),
('GS302', 'التقويم والقياس التربوي', 'closed', 2),
('GS303', 'الوسائل التعليمية', 'closed', 2),
('GS401', 'الصحة النفسية', 'closed', 2),
('GS402', 'التربية العملية 1', 'closed', 2),
('GS403', 'التربية العملية 2', 'closed', 2),
('IS100', 'الدراسات الاسلامية 1', 'closed', 2),
('IS101', 'الدراسات الاسلامية 2', 'open', 2),
('MM111', 'رياضة عامة 1', 'closed', 3),
('MM112', 'رياضة عامة 2', 'open', 3),
('MM200', 'هياكل رياضية', 'closed', 3),
('MM208', 'جبر خطي', 'closed', 2),
('ST103', 'مبادئ إحصاء واحتمالات', 'closed', 3);

-- --------------------------------------------------------

--
-- Table structure for table `course_schedule`
--

CREATE TABLE `course_schedule` (
  `id` int(11) NOT NULL,
  `course_id` varchar(5) NOT NULL,
  `day` varchar(10) NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_schedule`
--

INSERT INTO `course_schedule` (`id`, `course_id`, `day`, `time`) VALUES
(28, 'CS110', 'السبت', '08:00:00'),
(47, 'CS111', 'الخميس', '16:00:00'),
(51, 'AR216', 'الأحد', '12:00:00'),
(54, 'AR100', 'السبت', '08:00:00'),
(61, 'MM112', 'الاثنين', '08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Requirements`
--

CREATE TABLE `Requirements` (
  `course_id` varchar(6) NOT NULL,
  `Requirements_id` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Requirements`
--

INSERT INTO `Requirements` (`course_id`, `Requirements_id`) VALUES
('AR101', 'AR100'),
('AR213', 'AR101'),
('AR216', 'AR213'),
('CS101', 'CS100'),
('CS111', 'CS110'),
('CS112', 'CS110'),
('CS211', 'CS111'),
('CS212', 'MM111'),
('CS215', 'CS111'),
('CS217', 'CS111'),
('CS310', 'CS214'),
('CS312', 'CS111'),
('CS314', 'CS312'),
('CS315', 'CS312'),
('CS413', 'CS412'),
('CS422E', 'CS428'),
('EL101', 'EL100'),
('GS200', 'GS100'),
('GS201', 'GS101'),
('GS202', 'GS201'),
('GS203', 'GS200'),
('GS301', 'GS101'),
('GS302', 'GS301'),
('GS401', 'GS203'),
('GS402', 'GS301'),
('GS403', 'GS402'),
('IS101', 'IS100'),
('MM112', 'MM111'),
('MM200', 'MM111'),
('MM208', 'CS110'),
('ST103', 'MM111');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `class_number` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `semester`, `class_number`, `year`, `password`) VALUES
(22007701, 'أحمد علي ', 'خريف', 1, 2021, '22007701'),
(22007702, 'رحاب محمد', 'ربيع', 1, 2021, '22007702'),
(22007703, 'زينب شاهين', 'ربيع', 1, 2021, '22007703'),
(22007704, 'سمرا خالد', 'ربيع', 1, 2022, '22007704');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `passed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL,
  `student_id` int(10) NOT NULL,
  `course_id` varchar(5) NOT NULL,
  `day` varchar(10) DEFAULT NULL,
  `time` time DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`id`, `student_id`, `course_id`, `day`, `time`, `completed`) VALUES
(22, 22007703, 'CS110', 'السبت', '08:00:00', 0),
(25, 22007702, 'MM111', 'الخميس', '14:00:00', 1),
(61, 22007701, 'CS110', '2025-06-17', NULL, 1),
(62, 22007701, 'CS111', '2025-06-17', NULL, 1),
(73, 22007701, 'MM111', 'الاثنين', '10:00:00', 1),
(79, 22007701, 'MM112', 'الاثنين', '08:00:00', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_schedule`
--
ALTER TABLE `course_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `Requirements`
--
ALTER TABLE `Requirements`
  ADD PRIMARY KEY (`course_id`,`Requirements_id`),
  ADD KEY `Requirements_ibfk_2` (`Requirements_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student` (`student_id`),
  ADD KEY `fk_class` (`class_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_schedule`
--
ALTER TABLE `course_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_schedule`
--
ALTER TABLE `course_schedule`
  ADD CONSTRAINT `course_schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `Requirements`
--
ALTER TABLE `Requirements`
  ADD CONSTRAINT `Requirements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `Requirements_ibfk_2` FOREIGN KEY (`Requirements_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `fk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
