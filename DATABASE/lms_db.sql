-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 05:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `Book_ID` int(11) NOT NULL,
  `ISBN` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Cover_Image` varchar(255) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Author` varchar(255) DEFAULT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Publication_Date` varchar(255) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Total_Copies` int(11) DEFAULT NULL,
  `Available_Copies` int(11) DEFAULT NULL,
  `Borrowed_Copies` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`Book_ID`, `ISBN`, `Title`, `Cover_Image`, `Description`, `Author`, `Category`, `Publication_Date`, `Location`, `Total_Copies`, `Available_Copies`, `Borrowed_Copies`) VALUES
(1, '42342', 'Hello World', 'uploads/1760367941_1760359904_Atomic.png', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Error adipisci labore quia eum! Molestiae itaque vitae ipsum soluta eos, consectetur, harum accusantium, deleniti est doloribus earum labore quos eum similique! Ipsam voluptatibus quisquam, odio al', 'James', 'add_new', '2025-10-16', 'add_new', 12, 10, 2),
(2, '1231254', 'Programming', 'uploads/1760367982_1759213008_Book.jpg', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Error adipisci labore quia eum! Molestiae itaque vitae ipsum soluta eos, consectetur, harum accusantium, deleniti est doloribus earum labore quos eum similique! Ipsam voluptatibus quisquam, odio al', 'John', 'add_new', '2025-10-13', 'add_new', 12765, 12764, 1);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_record`
--

CREATE TABLE `borrow_record` (
  `Borrow_ID` int(11) NOT NULL,
  `User_Type` enum('student','faculty') NOT NULL,
  `Student_ID_Number` varchar(255) NOT NULL,
  `Book_ID` int(11) NOT NULL,
  `Borrow_Date` date NOT NULL,
  `Due_Date` date NOT NULL,
  `Return_Date` date DEFAULT NULL,
  `Fine` decimal(10,2) DEFAULT 0.00,
  `Status` enum('borrowed','returned','overdue') DEFAULT 'borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_record`
--

INSERT INTO `borrow_record` (`Borrow_ID`, `User_Type`, `Student_ID_Number`, `Book_ID`, `Borrow_Date`, `Due_Date`, `Return_Date`, `Fine`, `Status`) VALUES
(1, 'student', '123-123-123', 1, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(2, 'student', '123-123-123', 2, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(3, 'student', '123-123-123', 1, '2025-10-14', '2025-10-14', '2025-10-14', 0.00, 'returned'),
(4, 'student', '123-123-123', 2, '2025-10-14', '2025-10-14', '2025-10-14', 0.00, 'returned'),
(5, 'student', '123-123-123', 1, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(6, 'student', '123-123-123', 2, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(7, 'student', '123-123-123', 1, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(8, 'student', '123-123-123', 2, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(9, 'student', '246-246', 1, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned'),
(10, 'student', '246-246', 2, '2025-10-14', '2025-10-16', '2025-10-14', 0.00, 'returned');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`Category_ID`, `Category_Name`) VALUES
(1, 'Programming');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `Faculty_ID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Student_ID_Number` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Department` varchar(255) DEFAULT NULL,
  `Designation` varchar(255) DEFAULT NULL,
  `Picture` varchar(255) DEFAULT NULL,
  `Status` enum('inside','outside') DEFAULT 'outside'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `librarian`
--

CREATE TABLE `librarian` (
  `Librarian_ID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `picture` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian`
--

INSERT INTO `librarian` (`Librarian_ID`, `Name`, `Email`, `Password`, `picture`) VALUES
(1, 'Jonathan Mina', 'allen@gmail.com', '$2y$10$LEFSKs4C6AXgK//5.h38uOFamVr3PTaGaGsGApr6K/odj4sA.l4pO', '');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `Location_ID` int(11) NOT NULL,
  `Location_Name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Student_ID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Student_ID_Number` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Course` varchar(255) DEFAULT NULL,
  `Year_Level` varchar(255) DEFAULT NULL,
  `Picture` varchar(255) DEFAULT NULL,
  `Status` enum('inside','outside') DEFAULT 'outside',
  `Total_Points` decimal(6,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Student_ID`, `Name`, `Student_ID_Number`, `Email`, `Course`, `Year_Level`, `Picture`, `Status`, `Total_Points`) VALUES
(1, 'Allen', '123-123-123', 'alen@gmail.com', 'BSIT', '3rd', NULL, 'inside', 1.50),
(3, 'mina', '246-246', 'mina@gmail.com', 'BSIT', '1st', NULL, 'inside', 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `student_logs`
--

CREATE TABLE `student_logs` (
  `Log_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Time_In` datetime NOT NULL,
  `Time_Out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_points`
--

CREATE TABLE `student_points` (
  `id` int(11) NOT NULL,
  `student_id_no` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `points_earned` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_points`
--

INSERT INTO `student_points` (`id`, `student_id_no`, `date`, `points_earned`) VALUES
(2, '123-123-123', '2025-10-14', 1.50),
(3, '246-246', '2025-10-14', 1.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`Book_ID`),
  ADD UNIQUE KEY `ISBN` (`ISBN`);

--
-- Indexes for table `borrow_record`
--
ALTER TABLE `borrow_record`
  ADD PRIMARY KEY (`Borrow_ID`),
  ADD KEY `Book_ID` (`Book_ID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`Faculty_ID`),
  ADD UNIQUE KEY `Student_ID_Number` (`Student_ID_Number`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`Librarian_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`Location_ID`),
  ADD UNIQUE KEY `Location_Name` (`Location_Name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `Student_ID_Number` (`Student_ID_Number`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `student_logs`
--
ALTER TABLE `student_logs`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Student_ID` (`Student_ID`);

--
-- Indexes for table `student_points`
--
ALTER TABLE `student_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id_no` (`student_id_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `Book_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `borrow_record`
--
ALTER TABLE `borrow_record`
  MODIFY `Borrow_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `Faculty_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `librarian`
--
ALTER TABLE `librarian`
  MODIFY `Librarian_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `Location_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_logs`
--
ALTER TABLE `student_logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_points`
--
ALTER TABLE `student_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_record`
--
ALTER TABLE `borrow_record`
  ADD CONSTRAINT `borrow_record_ibfk_1` FOREIGN KEY (`Book_ID`) REFERENCES `book` (`Book_ID`);

--
-- Constraints for table `student_logs`
--
ALTER TABLE `student_logs`
  ADD CONSTRAINT `student_logs_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`);

--
-- Constraints for table `student_points`
--
ALTER TABLE `student_points`
  ADD CONSTRAINT `student_points_ibfk_1` FOREIGN KEY (`student_id_no`) REFERENCES `students` (`Student_ID_Number`);

CREATE TABLE `settings` (
  `Setting_ID` INT(11) NOT NULL AUTO_INCREMENT,
  `Library_Name` VARCHAR(255) NOT NULL,
  `Max_Borrow_Limit` INT(11) DEFAULT 3,
  `Borrow_Duration` INT(11) DEFAULT 7,       -- in days
  `Fine_Per_Day` DECIMAL(10,2) DEFAULT 0.00, -- in pesos
  `Open_Hour` TIME DEFAULT '08:00:00',
  `Close_Hour` TIME DEFAULT '17:00:00',
  PRIMARY KEY (`Setting_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
