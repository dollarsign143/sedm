-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `colleges`;
CREATE TABLE `colleges` (
  `college_uid` int(11) NOT NULL AUTO_INCREMENT,
  `college_abbrev` varchar(40) DEFAULT NULL,
  `college_name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`college_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `colleges` (`college_uid`, `college_abbrev`, `college_name`) VALUES
(1,	'CEIT',	'College of Engineering and Information Technology'),
(2,	'CTHM',	'College of Tourism and Hospitality Management'),
(3,	'CITTE',	'College of Industrial Technology and Teacher Education');

DROP TABLE IF EXISTS `curriculums`;
CREATE TABLE `curriculums` (
  `curriculum_uid` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_no` varchar(40) DEFAULT NULL,
  `curriculum_isLock` varchar(40) DEFAULT NULL,
  `curriculum_yearCreated` varchar(40) DEFAULT NULL,
  `curriculum_schoolYearCreated` varchar(40) DEFAULT NULL,
  `program_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`curriculum_uid`),
  KEY `programs_curriculums` (`program_uid`),
  CONSTRAINT `programs_curriculums` FOREIGN KEY (`program_uid`) REFERENCES `programs` (`program_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `curriculum_subjects`;
CREATE TABLE `curriculum_subjects` (
  `curricSubj_uid` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_uid` int(11) DEFAULT NULL,
  `subject_uid` int(11) DEFAULT NULL,
  `curricSubj_prerequisite1` varchar(40) DEFAULT NULL,
  `curricSubj_prerequisite2` varchar(40) DEFAULT NULL,
  `curricSubj_labUnits` int(11) DEFAULT NULL,
  `curricSubj_lecUnits` int(11) DEFAULT NULL,
  `curricSubj_labHours` int(11) DEFAULT NULL,
  `curricSubj_lecHours` int(11) DEFAULT NULL,
  `curricSubj_year` varchar(40) DEFAULT NULL,
  `curricSubj_sem` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`curricSubj_uid`),
  KEY `curriculums_curriculum_subjects` (`curriculum_uid`),
  KEY `subjects_curriculum_subjects` (`subject_uid`),
  CONSTRAINT `curriculums_curriculum_subjects` FOREIGN KEY (`curriculum_uid`) REFERENCES `curriculums` (`curriculum_uid`),
  CONSTRAINT `subjects_curriculum_subjects` FOREIGN KEY (`subject_uid`) REFERENCES `subjects` (`subject_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `program_uid` int(11) NOT NULL AUTO_INCREMENT,
  `program_abbrev` varchar(40) DEFAULT NULL,
  `program_name` varchar(150) DEFAULT NULL,
  `college_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`program_uid`),
  KEY `college_uid` (`college_uid`),
  CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`college_uid`) REFERENCES `colleges` (`college_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `programs` (`program_uid`, `program_abbrev`, `program_name`, `college_uid`) VALUES
(1,	'BSIT',	'Bachelor of Science in Information Technology',	1),
(2,	'DCT',	'Diploma of Computer Technology',	1),
(3,	'BSEE',	'Bachelor of Science in Electrical Engineering',	1);

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_uid` int(11) NOT NULL AUTO_INCREMENT,
  `student_schoolId` varchar(40) DEFAULT NULL,
  `student_yearLevel` varchar(40) DEFAULT NULL,
  `program_uid` int(11) DEFAULT NULL,
  `curriculum_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_uid`),
  KEY `programs_students` (`program_uid`),
  KEY `curriculums_students` (`curriculum_uid`),
  CONSTRAINT `curriculums_students` FOREIGN KEY (`curriculum_uid`) REFERENCES `curriculums` (`curriculum_uid`),
  CONSTRAINT `programs_students` FOREIGN KEY (`program_uid`) REFERENCES `programs` (`program_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `students_subjects`;
CREATE TABLE `students_subjects` (
  `studSubj_uid` int(11) NOT NULL AUTO_INCREMENT,
  `subject_uid` int(11) NOT NULL,
  `studSubj_remarks` varchar(25) NOT NULL,
  PRIMARY KEY (`studSubj_uid`),
  KEY `subject_uid` (`subject_uid`),
  CONSTRAINT `students_subjects_ibfk_1` FOREIGN KEY (`subject_uid`) REFERENCES `subjects` (`subject_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `student_profile`;
CREATE TABLE `student_profile` (
  `studProf_uid` int(11) NOT NULL AUTO_INCREMENT,
  `studProf_fname` varchar(40) DEFAULT NULL,
  `studProf_mname` varchar(40) DEFAULT NULL,
  `studProf_lname` varchar(40) DEFAULT NULL,
  `studProf_age` int(11) DEFAULT NULL,
  `studProf_gender` varchar(40) DEFAULT NULL,
  `student_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`studProf_uid`),
  KEY `students_student_profile` (`student_uid`),
  CONSTRAINT `students_student_profile` FOREIGN KEY (`student_uid`) REFERENCES `students` (`student_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `subject_uid` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(40) DEFAULT NULL,
  `subject_desc` varchar(255) DEFAULT NULL,
  `subject_isActive` varchar(40) DEFAULT NULL,
  `subjCat_uid` int(11) DEFAULT NULL,
  `college_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`subject_uid`),
  KEY `subjCat_uid` (`subjCat_uid`),
  KEY `college_uid` (`college_uid`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`subjCat_uid`) REFERENCES `subjects_category` (`subjCat_uid`),
  CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`college_uid`) REFERENCES `colleges` (`college_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `subjects` (`subject_uid`, `subject_code`, `subject_desc`, `subject_isActive`, `subjCat_uid`, `college_uid`) VALUES
(1,	'elective',	'Elective',	'active',	NULL,	NULL),
(4,	'Math 1.7',	'College Algebra',	'active',	3,	1),
(5,	'Math 2.1',	'Plane Spherical Trigonometry',	'active',	3,	1),
(6,	'Eng 1',	'Study and Thinking Skills',	'active',	1,	1),
(7,	'IT 1',	'Computer Concept and Fundamentals',	'active',	8,	1),
(8,	'Acctg 1',	'Basic Accounting I',	'active',	4,	1),
(9,	'Fil 1 ',	'Komunikasyon sa Akademikong Filipino',	'active',	2,	1),
(10,	'PE 1 ',	'Physical Fitness',	'active',	12,	1),
(11,	'NSTP 1',	'CMT/LTS',	'active',	13,	1),
(12,	'Math 6.4',	'Analytic Geometry',	'active',	3,	1),
(13,	'Math 4',	'Advance Algebra',	'active',	3,	1),
(14,	'CSc 101',	'Fund. Of Problem Solving and Programming I',	'active',	8,	1),
(15,	'SocSci 4',	'Philippine History with New Constitution',	'active',	7,	1),
(16,	'Eng 2',	'Writing In the Discipline',	'active',	1,	1),
(17,	'Acctg 2',	'Basic Accounting II',	'active',	4,	1),
(18,	'Fil 2',	'Pagbasa at Pagsulat tungo sa Pananaliksik',	'active',	2,	1),
(19,	'PE 2',	'Rhythmatic Activities',	'active',	12,	1),
(20,	'NSTP 2',	'CMT/LTS',	'active',	13,	1),
(21,	'IT 18 ',	'Graphics Applications',	'active',	14,	1),
(22,	'CSc 121',	'Data Structures I',	'active',	14,	1),
(23,	'Philo 2 ',	'Introduction to Logic',	'active',	11,	1),
(24,	'ES 101 ',	'Fundamentals of Environmental Sciences',	'active',	6,	1),
(25,	'IT 100',	'Operating System Utilities and Application I',	'active',	14,	1),
(26,	'Eng 3',	'Speech and Oral Communication',	'active',	1,	1),
(27,	'PE 3',	'Individual & Dual Sports',	'active',	12,	1),
(28,	'IT 101',	'Operating System Utilities and Application II',	'active',	14,	1),
(29,	'CSc 122',	'Data Structures II',	'active',	14,	1),
(30,	'CSc 141',	'Discrete Mathematics for Scientist & Engineers',	'active',	3,	1),
(31,	'IT 110 ',	'Web Page Design and Development ',	'active',	14,	1),
(32,	'IT 106',	'Concept and Application of RDBMS I',	'active',	14,	1),
(33,	'Hum 1',	'Introduction to Humanities ',	'active',	11,	1),
(34,	'PE 4',	'Major Sports/ Team Sports',	'active',	12,	1),
(35,	'ES 1',	'Engineering Drawing ',	'active',	5,	1),
(36,	'CSc 130 ',	'System Analysis and Design',	'active',	14,	1),
(37,	'IT 4',	'Quality Consciousness, Processes and Habits ',	'active',	14,	1),
(38,	'IT 111',	'Advance Web Page Design and Development ',	'active',	14,	1),
(39,	'IT 107',	'Concept and Application of RDBMS II',	'active',	14,	1),
(40,	'SocSci 22',	'Life and Works of Rizal',	'active',	7,	1),
(42,	'CSc 110',	'COMPUTER ORGANIZATION AND ARCHITECTURE, AND ASSEMBLY PROGRAMMING',	'active',	14,	1),
(43,	'SocSci 5',	'GENERAL ECONOMICS W/ TAXATION & LR',	'active',	7,	1),
(44,	'IT 3',	'ETHICS FOR IT PROFESSIONALS',	'active',	8,	1),
(45,	'CSc 131',	'Software Engineering ',	'active',	14,	1),
(46,	'IT 198',	'Research Problem',	'active',	9,	1),
(47,	'ES 2',	'Computer - Aided Drafting ',	'active',	5,	1),
(48,	'ESM 171',	'Intro to Remote Sensing and GIS',	'active',	14,	1),
(49,	'IT 197',	'ON THE JOB TRAINING',	'active',	16,	1),
(50,	'IT 115',	'Networking Administration and Installation I',	'active',	14,	1),
(51,	'IT 131',	'Management of Information Systems',	'active',	14,	1),
(52,	'SocSci 1',	'Gen Sociology with Population Education',	'active',	7,	1),
(53,	'IT 5',	'Introduction to IT Entrepreneurship ',	'active',	15,	1),
(54,	'IT 199',	'Undergraduate Thesis or Project',	'active',	9,	1),
(55,	'IT 116 ',	'Networking Administration and Installation II',	'active',	14,	1),
(57,	'CSc 150',	'Object Oriented Programming',	'active',	14,	1),
(58,	'CSc 140',	'Introduction to Artificial Intelligence ',	'active',	14,	1),
(59,	'Soc Sci 6',	'General Psychology ',	'active',	7,	1),
(62,	'CSc 155',	'Introduction to Computer Graphics ',	'active',	10,	1),
(63,	'IT 125 ',	'Interactive Multimedia Authoring ',	'active',	14,	1),
(64,	'156',	'Advanced Assembly Programming ',	'active',	10,	1),
(65,	'IT 6',	'eWaste Management',	'active',	14,	1);

DROP TABLE IF EXISTS `subjects_category`;
CREATE TABLE `subjects_category` (
  `subjCat_uid` int(11) NOT NULL AUTO_INCREMENT,
  `subjCat_name` varchar(40) NOT NULL,
  PRIMARY KEY (`subjCat_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `subjects_category` (`subjCat_uid`, `subjCat_name`) VALUES
(1,	'ENGLISH'),
(2,	'FILIPINO'),
(3,	'MATHEMATICS'),
(4,	'ACCOUNTING'),
(5,	'DRAWING'),
(6,	'NATURAL SCIENCE'),
(7,	'SOCIAL SCIENCE'),
(8,	'CORE SUBJECTS'),
(9,	'RESEARCH'),
(10,	'ELECTIVE SUBJECT'),
(11,	'HUMANITIES'),
(12,	'PHYSICAL EDUCATION'),
(13,	'NSTP'),
(14,	'MAJOR SUBJECTS'),
(15,	'ENTREPRENEURSHIP'),
(16,	'OJT');

-- 2020-03-29 13:12:10
