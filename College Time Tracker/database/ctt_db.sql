-- College Time Tracker Database
-- Compatible with XAMPP MySQL/MariaDB and phpMyAdmin

CREATE DATABASE IF NOT EXISTS `ctt_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ctt_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `timetablecelldetail`;
DROP TABLE IF EXISTS `timetablecell`;
DROP TABLE IF EXISTS `teacher_subject`;
DROP TABLE IF EXISTS `section`;
DROP TABLE IF EXISTS `subject`;
DROP TABLE IF EXISTS `class`;
DROP TABLE IF EXISTS `program`;
DROP TABLE IF EXISTS `room`;
DROP TABLE IF EXISTS `period`;
DROP TABLE IF EXISTS `timetable`;
DROP TABLE IF EXISTS `student`;
DROP TABLE IF EXISTS `teacher`;
DROP TABLE IF EXISTS `academicsincharge`;
DROP TABLE IF EXISTS `administrator`;

CREATE TABLE `administrator` (
  `AdminID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`AdminID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `academicsincharge` (
  `InchargeID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`InchargeID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username` (`Username`),
  KEY `CreatedBy` (`CreatedBy`),
  CONSTRAINT `fk_incharge_admin` FOREIGN KEY (`CreatedBy`) REFERENCES `administrator` (`AdminID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `teacher` (
  `TeacherID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Designation` varchar(100) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`TeacherID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username` (`Username`),
  KEY `CreatedBy` (`CreatedBy`),
  CONSTRAINT `fk_teacher_incharge` FOREIGN KEY (`CreatedBy`) REFERENCES `academicsincharge` (`InchargeID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `RollNo` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Semester` varchar(50) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`StudentID`),
  UNIQUE KEY `RollNo` (`RollNo`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username` (`Username`),
  KEY `CreatedBy` (`CreatedBy`),
  CONSTRAINT `fk_student_incharge` FOREIGN KEY (`CreatedBy`) REFERENCES `academicsincharge` (`InchargeID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `program` (
  `ProgramID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `AcademicSystemType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ProgramID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `class` (
  `ClassID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Session` varchar(50) DEFAULT NULL,
  `TTDName` varchar(100) DEFAULT NULL,
  `CurrentSemester` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `ProgramID` int(11) DEFAULT NULL,
  `ImportantDates` text DEFAULT NULL,
  `RowColor` varchar(20) DEFAULT '#ffffff',
  PRIMARY KEY (`ClassID`),
  KEY `ProgramID` (`ProgramID`),
  CONSTRAINT `fk_class_program` FOREIGN KEY (`ProgramID`) REFERENCES `program` (`ProgramID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `subject` (
  `SubjectID` int(11) NOT NULL AUTO_INCREMENT,
  `ClassID` int(11) DEFAULT NULL,
  `Name` varchar(100) NOT NULL,
  `Semester` varchar(50) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`SubjectID`),
  KEY `ClassID` (`ClassID`),
  CONSTRAINT `fk_subject_class` FOREIGN KEY (`ClassID`) REFERENCES `class` (`ClassID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `section` (
  `SectionID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `TTDName` varchar(100) DEFAULT NULL,
  `Semester` varchar(50) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `SubjectID` int(11) DEFAULT NULL,
  `TeacherID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SectionID`),
  KEY `SubjectID` (`SubjectID`),
  KEY `TeacherID` (`TeacherID`),
  CONSTRAINT `fk_section_subject` FOREIGN KEY (`SubjectID`) REFERENCES `subject` (`SubjectID`) ON DELETE SET NULL,
  CONSTRAINT `fk_section_teacher` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `room` (
  `RoomID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL,
  `TTDName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`RoomID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `period` (
  `PeriodID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) DEFAULT NULL,
  `Number` int(11) DEFAULT NULL,
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `FridayStartTime` time DEFAULT NULL,
  `FridayEndTime` time DEFAULT NULL,
  `IsZeroPeriod` tinyint(1) DEFAULT 0,
  `IsBreak` tinyint(1) DEFAULT 0,
  `DisplayOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`PeriodID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `timetable` (
  `TTID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `CreatedOn` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedOn` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `WithEffectiveFrom` date DEFAULT NULL,
  PRIMARY KEY (`TTID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `timetablecell` (
  `TTCID` int(11) NOT NULL AUTO_INCREMENT,
  `TTID` int(11) DEFAULT NULL,
  `ClassID` int(11) DEFAULT NULL,
  `PeriodID` int(11) DEFAULT NULL,
  PRIMARY KEY (`TTCID`),
  UNIQUE KEY `unique_tt_class_period` (`TTID`,`ClassID`,`PeriodID`),
  KEY `TTID` (`TTID`),
  KEY `ClassID` (`ClassID`),
  KEY `PeriodID` (`PeriodID`),
  CONSTRAINT `fk_cell_tt` FOREIGN KEY (`TTID`) REFERENCES `timetable` (`TTID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cell_class` FOREIGN KEY (`ClassID`) REFERENCES `class` (`ClassID`) ON DELETE CASCADE,
  CONSTRAINT `fk_cell_period` FOREIGN KEY (`PeriodID`) REFERENCES `period` (`PeriodID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `timetablecelldetail` (
  `TTCDID` int(11) NOT NULL AUTO_INCREMENT,
  `TTCID` int(11) DEFAULT NULL,
  `SubjectID` int(11) DEFAULT NULL,
  `TeacherID` int(11) DEFAULT NULL,
  `SectionID` int(11) DEFAULT NULL,
  `RoomID` int(11) DEFAULT NULL,
  `Days` varchar(80) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT NULL,
  `CellColor` varchar(20) DEFAULT '#ffffff',
  `Notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`TTCDID`),
  KEY `TTCID` (`TTCID`),
  KEY `SubjectID` (`SubjectID`),
  KEY `TeacherID` (`TeacherID`),
  KEY `SectionID` (`SectionID`),
  KEY `RoomID` (`RoomID`),
  CONSTRAINT `fk_detail_cell` FOREIGN KEY (`TTCID`) REFERENCES `timetablecell` (`TTCID`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_subject` FOREIGN KEY (`SubjectID`) REFERENCES `subject` (`SubjectID`) ON DELETE SET NULL,
  CONSTRAINT `fk_detail_teacher` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`) ON DELETE SET NULL,
  CONSTRAINT `fk_detail_section` FOREIGN KEY (`SectionID`) REFERENCES `section` (`SectionID`) ON DELETE SET NULL,
  CONSTRAINT `fk_detail_room` FOREIGN KEY (`RoomID`) REFERENCES `room` (`RoomID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `teacher_subject` (
  `TSID` int(11) NOT NULL AUTO_INCREMENT,
  `TeacherID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `AssignedAt` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`TSID`),
  UNIQUE KEY `unique_assign` (`TeacherID`,`SubjectID`),
  KEY `SubjectID` (`SubjectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `administrator` (`AdminID`,`Name`,`Email`,`Username`,`Password`) VALUES
(1,'Admin','admin@ctt.com','admin',MD5('admin123'));

INSERT INTO `academicsincharge` (`InchargeID`,`Name`,`Email`,`Username`,`Password`,`Department`,`CreatedBy`) VALUES
(1,'Academic Incharge','incharge@ctt.com','incharge',MD5('123456'),'Computer Science',1);

INSERT INTO `teacher` (`TeacherID`,`Name`,`Email`,`Username`,`Password`,`Department`,`Designation`,`CreatedBy`) VALUES
(1,'Muddassar Manzoor','muddassar@ctt.com','muddassar',MD5('123456'),'Psychology','Lecturer',1),
(2,'English Department','english@ctt.com','englishdept',MD5('123456'),'English','Department',1),
(3,'Wania Iqbal','wania@ctt.com','wania',MD5('123456'),'Mathematics','Lecturer',1),
(4,'M. Naveed','naveed@ctt.com','naveed',MD5('123456'),'Computer Science','Lecturer',1),
(5,'Farheen Zahid','farheen@ctt.com','farheen',MD5('123456'),'Computer Science','Lecturer',1),
(6,'Abdul Bari','abdulbari@ctt.com','abdulbari',MD5('123456'),'Islamic Studies','Lecturer',1),
(7,'Roman Asad','roman@ctt.com','roman',MD5('123456'),'Islamic Studies','Lecturer',1),
(8,'Sajjad Abdullah','sajjad@ctt.com','sajjad',MD5('123456'),'Computer Science','Assistant Professor',1),
(9,'Muzzam Rasheed','muzzam@ctt.com','muzzam',MD5('123456'),'English','Lecturer',1),
(10,'Mubashir Ali Shah','mubashir@ctt.com','mubashir',MD5('123456'),'English','Lecturer',1),
(11,'Shahzad Qadir','shahzad@ctt.com','shahzad',MD5('123456'),'Linguistics','Lecturer',1),
(12,'Hazoor Bakhsh Islam','hazoor@ctt.com','hazoor',MD5('123456'),'Mathematics','Lecturer',1),
(13,'Dr. Abdur Razzaq','abdurrazzaq@ctt.com','abdurrazzaq',MD5('123456'),'Computer Science','Professor',1),
(14,'Zunurain Sarwar','zunurain@ctt.com','zunurain',MD5('123456'),'Business','Lecturer',1),
(15,'Zunnurain Sarwar','zunnurain@ctt.com','zunnurain',MD5('123456'),'Business','Lecturer',1);

INSERT INTO `student` (`StudentID`,`Name`,`RollNo`,`Email`,`Username`,`Password`,`Department`,`Semester`,`CreatedBy`) VALUES
(1,'Ayesha','14','ayesha14@gmail.com','ayesha',MD5('123456'),'CS','8th',1),
(2,'Nasreen','02','nasreen02@gmail.com','nasreen',MD5('123456'),'CS','2nd',1),
(3,'Kiran','06','kiran06@gmail.com','kiran',MD5('123456'),'CS','4th',1),
(4,'Aliza','08','alizanoor@gmail.com','aliza',MD5('123456'),'Cyber Security','2nd',1);

INSERT INTO `program` (`ProgramID`,`Name`,`AcademicSystemType`) VALUES
(1,'BSCS','Semester'),(2,'ADPCS','Semester'),(3,'ADPEM','Semester'),(4,'BSIT','Semester'),(5,'BSAI','Semester');

INSERT INTO `class` (`ClassID`,`Name`,`Session`,`TTDName`,`CurrentSemester`,`IsActive`,`ProgramID`,`ImportantDates`,`RowColor`) VALUES
(1,'KFUEIT BSCS 2529','2025-2029','Morning Shift Timetable','2nd',1,1,'Mid Term Exam\n13.04.26 to 18.04.26\nLD to Submit MT Marks\n25.04.26\nLD to Submit Sessional & Attend\n13.06.26\nFinal Term\n15.06.26','#92d050'),
(2,'IUB ADPCS 2S27 - 2nd','2025-2027','Morning Shift Timetable','2nd',1,2,'','#ed7d31'),
(3,'ADPEM 2S27 - 2nd','2025-2027','Morning Shift Timetable','2nd',1,3,'','#ed7d31'),
(4,'IUB BSCS 2A28 / ADPCS 2A26 - 3rd','2024-2028','Morning Shift Timetable','3rd',1,1,'Midterm Exam\n09.02.2026 to 14.02.2026\nLD to enter Teacher data\n02.01.2026\nLD to enter Mid & Sessional Marks\n25.02.2026\nFinal Term Exam\nIn the month of April','#d9d9d9'),
(5,'IUB BSCS2327 / ADPCS2325 - 4th','2023-2027','Morning Shift Timetable','4th',1,1,'Mid Term Exam\n08.12.25 to 13.12.25\nLD to Submit MT & Sess. Marks\n30.12.25\nFinal Term\n30.01.26','#d9d9d9'),
(6,'IUB BSCS 2C26 - 5th','2022-2026','Morning Shift Timetable','5th',1,1,'Midterm Exam\n02.02.26\nLD to Submit MT & Sess. Marks\n10.03.26\nFinal Term\n26.03.26','#d9d9d9'),
(7,'IUB BSCS 2325 - 8th','2023-2025','Morning Shift Timetable','8th',1,1,'Midterm Exam\n29-06-2026\nFinal Term Exam\n17-08-2026 (Tentative)','#ed7d31');

INSERT INTO `period` (`PeriodID`,`Title`,`Number`,`StartTime`,`EndTime`,`FridayStartTime`,`FridayEndTime`,`IsZeroPeriod`,`IsBreak`,`DisplayOrder`) VALUES
(1,'Period 1',1,'08:30:00','09:20:00','08:30:00','09:15:00',0,0,1),
(2,'Period 2',2,'09:20:00','10:05:00','09:15:00','09:55:00',0,0,2),
(3,'Period 3',3,'10:05:00','10:50:00','09:55:00','10:35:00',0,0,3),
(4,'Period 4',4,'10:50:00','11:35:00','10:35:00','11:15:00',0,0,4),
(5,'Period 5',5,'11:35:00','12:20:00','11:15:00','11:50:00',0,0,5),
(6,'Period 6',6,'12:20:00','13:00:00','11:50:00','12:15:00',0,0,6),
(7,'Period 7',7,'13:00:00','13:40:00','12:15:00','12:40:00',0,0,7);

INSERT INTO `room` (`RoomID`,`Title`,`TTDName`) VALUES
(1,'Room No. 15','Morning Shift Timetable'),(2,'Room No. 19','Morning Shift Timetable'),(3,'Physics Lab 2','Morning Shift Timetable'),(4,'Comp. Lab 1','Morning Shift Timetable'),(5,'Comp. Lab 2','Morning Shift Timetable'),(6,'Room No. 18','Morning Shift Timetable'),(7,'Room No. 16','Morning Shift Timetable'),(8,'Computer LAB 2','Morning Shift Timetable'),(9,'Computer LAB 1','Morning Shift Timetable');

INSERT INTO `subject` (`SubjectID`,`ClassID`,`Name`,`Semester`,`DisplayOrder`) VALUES
(1,1,'Intro. to Psychology','2nd',1),(2,1,'IAC of Pakistan','2nd',2),(3,1,'Underst. of Quran-II','2nd',3),(4,1,'Functional English','2nd',4),(5,1,'Quant. Reasoning-II','2nd',5),(6,1,'OO Programming','2nd',6),(7,1,'Digital Logic Design','2nd',7),(8,1,'Calculus-II','2nd',8),
(9,2,'Applications of ICT (2+1)','2nd',1),(10,2,'OO Programming (2+1)','2nd',2),(11,2,'Expository Writing (3+0)','2nd',3),(12,2,'Discrete Structures (3+0)','2nd',4),(13,2,'Quant. Reasoning-I (3+0)','2nd',5),(14,2,'Islamic Studies / Ethics (2+0)','2nd',6),
(15,3,'Applications of ICT (2+1)','2nd',1),(16,3,'Literary Forms and Movement (3+0)','2nd',2),(17,3,'Expository Writing (3+0)','2nd',3),(18,3,'Intro. To Phonetics and Phonology (3+0)','2nd',4),(19,3,'Quant. Reasoning-I (3+0)','2nd',5),(20,3,'Islamic Studies / Ethics (2+0)','2nd',6),
(21,4,'Data Structures','3rd',1),(22,4,'Computer Networks','3rd',2),(23,4,'Quant. Reasoning-II','3rd',3),(24,4,'Artificial Intelligence','3rd',4),(25,4,'Pak. Studies','3rd',5),(26,4,'Civics & Com. Eng','3rd',6),
(27,5,'Computer Architecture','4th',1),(28,5,'Database Systems','4th',2),(29,5,'Entrepreneurship','4th',3),(30,5,'Underst. of Quran','4th',4),(31,5,'Visual Programming','4th',5),(32,5,'Theory of Automata','4th',6),(33,5,'Software Engineering','4th',7),
(34,6,'Computer Architecture','5th',1),(35,6,'Information Security','5th',2),(36,6,'Entrepreneurship','5th',3),(37,6,'Visual Programming','5th',4),(38,6,'Numerical Computing','5th',5),(39,6,'Cloud Computing','5th',6),
(40,7,'Final Year Project - II','8th',1),(41,7,'Dist. Computing','8th',2),(42,7,'Mobile App Dev.','8th',3),(43,7,'Software Quality Eng.','8th',4),(44,7,'Arabic for Und. Quran','8th',5),(45,7,'Psychology','8th',6);

INSERT INTO `section` (`SectionID`,`Name`,`TTDName`,`Semester`,`DisplayOrder`,`IsActive`,`SubjectID`,`TeacherID`) VALUES
(1,'Regular','Morning Shift Timetable','2nd',1,1,1,1),(2,'Morning','Morning Shift Timetable','3rd',1,1,21,8),(3,'A','Morning Shift Timetable','8th',1,1,40,8);

INSERT INTO `timetable` (`TTID`,`Name`,`WithEffectiveFrom`) VALUES (1,'Morning Shift Timetable 2026','2026-02-01');

-- Create all grid cells for 7 classes x 7 periods
INSERT INTO `timetablecell` (`TTCID`,`TTID`,`ClassID`,`PeriodID`) VALUES
(1,1,1,1),(2,1,1,2),(3,1,1,3),(4,1,1,4),(5,1,1,5),(6,1,1,6),(7,1,1,7),
(8,1,2,1),(9,1,2,2),(10,1,2,3),(11,1,2,4),(12,1,2,5),(13,1,2,6),(14,1,2,7),
(15,1,3,1),(16,1,3,2),(17,1,3,3),(18,1,3,4),(19,1,3,5),(20,1,3,6),(21,1,3,7),
(22,1,4,1),(23,1,4,2),(24,1,4,3),(25,1,4,4),(26,1,4,5),(27,1,4,6),(28,1,4,7),
(29,1,5,1),(30,1,5,2),(31,1,5,3),(32,1,5,4),(33,1,5,5),(34,1,5,6),(35,1,5,7),
(36,1,6,1),(37,1,6,2),(38,1,6,3),(39,1,6,4),(40,1,6,5),(41,1,6,6),(42,1,6,7),
(43,1,7,1),(44,1,7,2),(45,1,7,3),(46,1,7,4),(47,1,7,5),(48,1,7,6),(49,1,7,7);

INSERT INTO `timetablecelldetail` (`TTCID`,`SubjectID`,`TeacherID`,`SectionID`,`RoomID`,`Days`,`DisplayOrder`,`CellColor`,`Notes`) VALUES
(1,1,1,1,1,'MO-TU',1,'#ffffff',''),(1,2,6,1,1,'WE-TH',2,'#ffffff',''),(1,3,7,1,1,'FR-SA',3,'#ffffff',''),
(2,4,2,1,1,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(3,5,3,1,1,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(4,6,4,1,2,'MO-TU-WE-TH-FR-SA',1,'#a9d18e',''),(5,7,5,1,2,'MO-TU-WE-TH-FR-SA',1,'#ffd966',''),(6,8,3,1,1,'MO-TU-WE-TH-FR-SA',1,'#ffffff','For Medical students only'),
(8,9,5,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffd966',''),(9,10,4,1,2,'MO-TU-WE-TH-FR-SA',1,'#a9d18e',''),(10,11,2,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(11,12,8,1,5,'MO-TU-WE',1,'#9dc3e6',''),(12,13,3,1,3,'MO-TU-WE',1,'#ffffff',''),(13,14,7,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),
(15,15,5,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffd966',''),(16,16,9,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(17,17,10,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(18,18,11,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(19,19,3,1,3,'MO-TU-WE',1,'#ffffff',''),(20,20,7,1,3,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),
(22,21,8,2,5,'MO-TU-WE',1,'#d9d9d9',''),(23,22,5,2,4,'MO-TU-WE',1,'#d9d9d9',''),(24,23,12,2,6,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),(25,24,13,2,4,'MO-TU-WE',1,'#d9d9d9',''),(25,25,6,2,6,'TH-FR-SA',2,'#d9d9d9',''),(26,26,15,2,6,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),
(29,27,13,2,4,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),(30,28,4,2,7,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),(31,29,14,2,7,'MO-TU-WE',1,'#d9d9d9',''),(31,30,7,2,1,'TH-FR-SA',2,'#d9d9d9',''),(32,31,8,2,5,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),(33,32,13,2,4,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),(34,33,5,2,7,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),
(36,34,13,2,4,'TH-FR-SA',1,'#d9d9d9',''),(37,35,13,2,4,'TH-FR-SA',1,'#d9d9d9',''),(38,36,15,2,7,'MO-TU-WE',1,'#d9d9d9',''),(39,37,8,2,5,'TH-FR-SA',1,'#d9d9d9',''),(40,38,3,2,1,'TH-FR-SA',1,'#d9d9d9',''),(41,39,8,2,5,'MO-TU-WE-TH-FR-SA',1,'#d9d9d9',''),
(43,40,8,3,8,'MO-TU-WE-TH-FR-SA',1,'#f4b183',''),(44,41,13,3,9,'MO-TU-WE-TH-FR-SA',1,'#9dc3e6',''),(45,42,4,3,2,'MO-TU-WE-TH-FR-SA',1,'#a9d18e',''),(46,43,5,3,7,'MO-TU-WE-TH-FR-SA',1,'#ffd966',''),(47,44,7,3,1,'MO-TU-WE-TH-FR-SA',1,'#ffffff',''),(48,45,1,3,1,'MO-TU-WE-TH-FR-SA',1,'#ffffff','');

INSERT INTO `teacher_subject` (`TeacherID`,`SubjectID`) SELECT `TeacherID`,`SubjectID` FROM `timetablecelldetail` WHERE `TeacherID` IS NOT NULL AND `SubjectID` IS NOT NULL GROUP BY `TeacherID`,`SubjectID`;
