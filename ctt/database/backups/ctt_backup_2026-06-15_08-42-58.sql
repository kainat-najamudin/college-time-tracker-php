-- CTT Database Backup
-- Generated: 2026-06-15 08:42:58
-- Database: ctt_db

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `academicsincharge`;
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
  CONSTRAINT `academicsincharge_ibfk_1` FOREIGN KEY (`CreatedBy`) REFERENCES `administrator` (`AdminID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `administrator` VALUES
('1','Admin','admin@ctt.com','admin','0192023a7bbd73250516f069df18b500','2026-06-14 22:49:20');

DROP TABLE IF EXISTS `class`;
CREATE TABLE `class` (
  `ClassID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Session` varchar(50) DEFAULT NULL,
  `TTDName` varchar(100) DEFAULT NULL,
  `CurrentSemester` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `ProgramID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ClassID`),
  KEY `ProgramID` (`ProgramID`),
  CONSTRAINT `class_ibfk_1` FOREIGN KEY (`ProgramID`) REFERENCES `program` (`ProgramID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `period`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `program`;
CREATE TABLE `program` (
  `ProgramID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `AcademicSystemType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ProgramID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `room`;
CREATE TABLE `room` (
  `RoomID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL,
  `TTDName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`RoomID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `section`;
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
  CONSTRAINT `section_ibfk_1` FOREIGN KEY (`SubjectID`) REFERENCES `subject` (`SubjectID`),
  CONSTRAINT `section_ibfk_2` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `student`;
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
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`CreatedBy`) REFERENCES `academicsincharge` (`InchargeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `subject`;
CREATE TABLE `subject` (
  `SubjectID` int(11) NOT NULL AUTO_INCREMENT,
  `ClassID` int(11) DEFAULT NULL,
  `Name` varchar(100) NOT NULL,
  `Semester` varchar(50) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`SubjectID`),
  KEY `ClassID` (`ClassID`),
  CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`ClassID`) REFERENCES `class` (`ClassID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `teacher`;
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
  CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`CreatedBy`) REFERENCES `academicsincharge` (`InchargeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `timetable`;
CREATE TABLE `timetable` (
  `TTID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `CreatedOn` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedOn` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `WithEffectiveFrom` date DEFAULT NULL,
  PRIMARY KEY (`TTID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `timetablecell`;
CREATE TABLE `timetablecell` (
  `TTCID` int(11) NOT NULL AUTO_INCREMENT,
  `TTID` int(11) DEFAULT NULL,
  `ClassID` int(11) DEFAULT NULL,
  `PeriodID` int(11) DEFAULT NULL,
  PRIMARY KEY (`TTCID`),
  KEY `TTID` (`TTID`),
  KEY `ClassID` (`ClassID`),
  KEY `PeriodID` (`PeriodID`),
  CONSTRAINT `timetablecell_ibfk_1` FOREIGN KEY (`TTID`) REFERENCES `timetable` (`TTID`),
  CONSTRAINT `timetablecell_ibfk_2` FOREIGN KEY (`ClassID`) REFERENCES `class` (`ClassID`),
  CONSTRAINT `timetablecell_ibfk_3` FOREIGN KEY (`PeriodID`) REFERENCES `period` (`PeriodID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `timetablecelldetail`;
CREATE TABLE `timetablecelldetail` (
  `TTCDID` int(11) NOT NULL AUTO_INCREMENT,
  `TTCID` int(11) DEFAULT NULL,
  `SubjectID` int(11) DEFAULT NULL,
  `TeacherID` int(11) DEFAULT NULL,
  `SectionID` int(11) DEFAULT NULL,
  `RoomID` int(11) DEFAULT NULL,
  `Days` varchar(50) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`TTCDID`),
  KEY `TTCID` (`TTCID`),
  KEY `SubjectID` (`SubjectID`),
  KEY `TeacherID` (`TeacherID`),
  KEY `SectionID` (`SectionID`),
  KEY `RoomID` (`RoomID`),
  CONSTRAINT `timetablecelldetail_ibfk_1` FOREIGN KEY (`TTCID`) REFERENCES `timetablecell` (`TTCID`),
  CONSTRAINT `timetablecelldetail_ibfk_2` FOREIGN KEY (`SubjectID`) REFERENCES `subject` (`SubjectID`),
  CONSTRAINT `timetablecelldetail_ibfk_3` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`),
  CONSTRAINT `timetablecelldetail_ibfk_4` FOREIGN KEY (`SectionID`) REFERENCES `section` (`SectionID`),
  CONSTRAINT `timetablecelldetail_ibfk_5` FOREIGN KEY (`RoomID`) REFERENCES `room` (`RoomID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
