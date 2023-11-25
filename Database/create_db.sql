USE iis;

DROP TABLE IF EXISTS `USERS`;
DROP TABLE IF EXISTS `SUBJECTS`;
DROP TABLE IF EXISTS `ACTIVITY`;
DROP TABLE IF EXISTS `ROOM`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_TEACHER`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_ACTIVITY`;
DROP TABLE IF EXISTS `DAY_TIME`;
DROP TABLE IF EXISTS `STUDENT_ACTIVITIES`;
DROP TABLE IF EXISTS `SUBJECT_TEACHER`;

CREATE TABLE USERS (
    /*PK*/
    user_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    username VARCHAR(50) NOT NULL UNIQUE,
    hashed_password VARCHAR(255) NOT NULL,  
    email VARCHAR(50) NOT NULL ,
    user_role ENUM('Admin', 'Guarantor', 'Teacher', 'Scheduler', 'Student') NOT NULL
);
CREATE TABLE ROOM (
    /*PK*/
    room_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    room_name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    room_location VARCHAR(4) NOT NULL
);
CREATE TABLE DAY_TIME (
    /*PK*/
    day_time_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    week_day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday','Friday','Saturday','Sunday')NOT NULL,
    time_range VARCHAR(250) NOT NULL
);
CREATE TABLE PREFERED_SLOTS_TEACHER (
    /*PK*/
    teacher_slot_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    user_ID INT REFERENCES USERS(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    preference ENUM('Prefers', 'Disprefers')NOT NULL
);

CREATE TABLE STUDENT_ACTIVITIES(
    /*PK*/
    student_subjects_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    student_ID INT REFERENCES USERS(user_ID),
    activity_ID int REFERENCES ACTIVITY(activity_ID)
);

CREATE TABLE SUBJECTS (
    /*PK*/
    subject_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    guarantor_ID INT REFERENCES USERS(user_ID),
    /*Attributes*/
    title VARCHAR(50) NOT NULL,
    abbervation VARCHAR(4) NOT NULL UNIQUE,
    credits INT NOT NULL,
    subj_description VARCHAR(500)
);

CREATE TABLE ACTIVITY (
    /*PK*/
    activity_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    subject_ID INT REFERENCES SUBJECTS(subject_ID),
    room_ID INT REFERENCES ROOM(room_ID),
    teacher_ID INT REFERENCES USERS(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    duration INT NOT NULL,
    repetition ENUM ('everyWeek', 'evenWeek', 'oddWeek', 'oneTime') NOT NULL,
    activity_date DATE NULL, -- YYYY-MM-DD
    activity_type ENUM ('Lecture', 'Tutorial', 'Seminar', 'Exam', 'Consultation', 'Exercise', 'Demo') NOT NULL
);

CREATE TABLE PREFERED_SLOTS_ACTIVITY (
    /*PK*/
    activity_slot_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    activity_ID INT REFERENCES ACTIVITY(activity_ID),
    room_ID INT REFERENCES ROOM(room_ID),
    teacher_ID INT REFERENCES USERS(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    preference ENUM('Prefers', 'Disprefers')NOT NULL
);

CREATE TABLE SUBJECT_TEACHER(
    /*PK*/
    sub_teach_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    user_ID INT REFERENCES USERS(user_ID),
    subject_ID INT REFERENCES SUBJECTS(subject_ID)
);

-- Inserting mock data into USERS
INSERT INTO USERS(username, hashed_password, email, user_role) VALUES
('Admin', '$2y$10$C/go1uYSt.sm42Pj.mDsyOydy9njlP/58LuEWM5xENdPxaUhMNyy6', 'user1@email.com', 'Admin'),     /*Password1*/
('Guarantor', '$2y$10$VwXVj7jiMGQV1SZLu7d8aec5weGdbCalIr3prbk2KmyMmva5VOaQe', 'user2@email.com', 'Guarantor'), /*Password2*/
('Teacher', '$2y$10$YGQm3PkwGsUo2bvgFexIm.WtJCvTs2jBXCcJFZpfYpXRpvJTX1eU2', 'user3@email.com', 'Teacher'),   /*Password3*/
('Scheduler', '$2y$10$k7mLRkie30rMGvUlLLa22unFuDavky9VRsa1SU05Y3CEYBdVLhnC2', 'user4@email.com', 'Scheduler'),  /*Password4*/
('Student', '$2y$10$4N/HgdC0BiAF7qkJdTqveOwQ5YdBFOW/l3sa9qcjNpF5f8vtySQBm  ', 'user5@email.com', 'Student'),  /*Password5*/
('Student1', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student1@email.com', 'Student'),/*Student123*/
('Student2', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student2@email.com', 'Student'),
('Student3', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student3@email.com', 'Student'),
('Student4', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student4@email.com', 'Student'),
('Student5', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student5@email.com', 'Student'),
('Student6', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student6@email.com', 'Student'),
('Student7', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student7@email.com', 'Student'),
('Student8', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student8@email.com', 'Student'),
('Student9', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student9@email.com', 'Student'),
('Student10', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student10@email.com', 'Student'),
('Student11', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student11@email.com', 'Student'),
('Student12', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student12@email.com', 'Student'),
('Student13', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student13@email.com', 'Student'),
('Student14', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student14@email.com', 'Student'),
('Student15', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student15@email.com', 'Student'),
('Student16', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student16@email.com', 'Student'),
('Student17', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student17@email.com', 'Student'),
('Student18', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student18@email.com', 'Student'),
('Student19', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student19@email.com', 'Student'),
('Student20', '$2y$10$a7quoLmJEYrgTxIclPMgxuqhlRG0w8NKeWHFRpt/piJ5MT/GDuJ42', 'student20@email.com', 'Student'),
('Guarantor1', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor1@email.com', 'Guarantor'),    /*Guarantor123*/
('Guarantor2', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor2@email.com', 'Guarantor'),
('Guarantor3', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor3@email.com', 'Guarantor'),
('Guarantor4', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor4@email.com', 'Guarantor'),
('Guarantor5', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor5@email.com', 'Guarantor'),
('Guarantor6', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor6@email.com', 'Guarantor'),
('Guarantor7', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor7@email.com', 'Guarantor'),
('Guarantor8', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor8@email.com', 'Guarantor'),
('Guarantor9', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor9@email.com', 'Guarantor'),
('Guarantor10', '$2y$10$YaijvFZmCni.Bbq.5JFBJeUcpLVvu5YgnGWsokO.AdpGmYoux5xo6', 'guarantor10@email.com', 'Guarantor'),
('Admin1', '$2y$10$z6ji3Q9hKAqX0sfRPxqQc.9K994jcPs/Vmkxop3VvDBdYO42d6Cri', 'admin1@email.com', 'Admin'),/*Admin123*/
('Admin2', '$2y$10$z6ji3Q9hKAqX0sfRPxqQc.9K994jcPs/Vmkxop3VvDBdYO42d6Cri', 'admin2@email.com', 'Admin'),
('Teacher1', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher1@email.com', 'Teacher'),
('Teacher2', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher2@email.com', 'Teacher'),
('Teacher3', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher3@email.com', 'Teacher'),
('Teacher4', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher4@email.com', 'Teacher'),
('Teacher5', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher5@email.com', 'Teacher'),
('Teacher6', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher6@email.com', 'Teacher'),
('Teacher7', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher7@email.com', 'Teacher'),
('Teacher8', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher8@email.com', 'Teacher'),
('Teacher9', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher9@email.com', 'Teacher'),
('Teacher10', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher10@email.com', 'Teacher'),
('Teacher11', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher11@email.com', 'Teacher'),
('Teacher12', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher12@email.com', 'Teacher'),
('Teacher13', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher13@email.com', 'Teacher'),
('Teacher14', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher14@email.com', 'Teacher'),
('Teacher15', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher15@email.com', 'Teacher'),
('Teacher16', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher16@email.com', 'Teacher'),
('Teacher17', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher17@email.com', 'Teacher'),
('Teacher18', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher18@email.com', 'Teacher'),
('Teacher19', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher19@email.com', 'Teacher'),
('Teacher20', '$2y$10$JJw5h3XPLCq6O2mqVvvXB.kmiQWzs4.cSzlHyGDJvuB8KseTLya3.', 'teacher20@email.com', 'Teacher');
-- Inserting mock data into ROOM
INSERT INTO ROOM (room_name, capacity, room_location) VALUES
('Chemistry Lab', 40, 'B101'),
('Computer Lab 1', 35, 'C201'),
('Physics Lab', 45, 'D301'),
('Biology Lab', 30, 'E105'),
('Mathematics Room', 25, 'F210'),
('Engineering Workshop', 50, 'G115'),
('Art Studio', 20, 'H220'),
('Music Room', 15, 'I330'),
('Lecture Hall 1', 100, 'J120'),
('Drama Theatre', 60, 'K210'),
('History Room', 30, 'L101'),
('Language Lab', 25, 'M220'),
('Geography Room', 35, 'N310'),
('Computer Lab 2', 30, 'O210'),
('Robotics Lab', 40, 'P115'),
('Economics Room', 20, 'Q220'),
('Psychology Lab', 25, 'R330'),
('Sociology Room', 30, 'S120'),
('Lecture Hall 2', 80, 'T210'),
('Library Study Room', 20, 'U101');



-- Inserting mock data into DAY_TIME
INSERT INTO DAY_TIME(week_day, time_range) VALUES
('Monday', '11:00-13:00'),
('Monday', '13:00-15:00'),
('Monday', '15:00-17:00'),
('Tuesday', '9:00-11:00'),
('Tuesday', '13:00-15:00'),
('Tuesday', '15:00-17:00'),
('Wednesday', '9:00-11:00'),
('Wednesday', '15:00-17:00'),
('Thursday', '9:00-11:00'),
('Thursday', '11:00-13:00'),
('Thursday', '13:00-15:00'),
('Thursday', '15:00-17:00'),
('Friday', '9:00-11:00'),
('Friday', '11:00-13:00'),
('Friday', '13:00-15:00'),
('Friday', '15:00-17:00');

-- Inserting mock data into SUBJECTS
INSERT INTO SUBJECTS(guarantor_ID, title, abbervation, credits, subj_description) VALUES
(2, 'Mathematics', 'MATH', 3, 'Fundamental course covering algebra, calculus, and geometry'),
(2, 'Physics', 'PHYS', 4, 'Comprehensive study of matter, energy, and motion'),
(2, 'Biology', 'BIOL', 4, 'Exploration of living organisms and life processes'),
(28, 'History', 'HIST', 3, 'In-depth analysis of historical events and periods'),
(28, 'English Literature', 'ENGL', 3, 'Study of classic and contemporary literary works'),
(28, 'Computer Science', 'COMP', 5, 'Course on programming, algorithms, and system design'),
(29, 'Art History', 'ARTH', 2, 'Survey of art movements and key artists through history'),
(29, 'Economics', 'ECON', 3, 'Understanding economic theories, models, and applications'),
(29, 'Psychology', 'PSYC', 3, 'Introduction to mental processes and behavior studies'),
(30, 'Chemistry', 'CHEM', 2, 'Introduction to chemical reactions and compounds'),
(30, 'Philosophy', 'PHIL', 3, 'Exploration of fundamental questions about existence, knowledge, and ethics'),
(30, 'Sociology', 'SOCI', 3, 'Study of society, social institutions, and social relationships'),
(31, 'Political Science', 'POLI', 4, 'Analysis of political systems, behavior, and political thought'),
(31, 'Environmental Science', 'ENVS', 3, 'Study of the environment and solutions to environmental challenges'),
(NULL, 'Music Theory', 'MUSC', 2, 'Understanding the fundamentals of music composition and performance'),
(NULL, 'Graphic Design', 'GRDS', 3, 'Principles of visual design and graphic communication'),
(NULL, 'Physical Education', 'PHED', 1, 'Focus on physical fitness, wellness, and sports'),
(NULL, 'Astronomy', 'ASTR', 3, 'Study of celestial bodies and the universe'),
(NULL, 'Theatre and Drama', 'DRAM', 4, 'Exploration of theatrical performance and production'),
(NULL, 'Anthropology', 'ANTH', 3, 'Study of human societies, cultures, and their development');
-- Inserting mock data into PREFERED_SLOTS_TEACHER
INSERT INTO PREFERED_SLOTS_TEACHER(user_ID, day_time_ID, preference) VALUES
(3, 1, 'Prefers'),
(3, 2, 'Disprefers'),
(1, 3, 'Prefers'),
(47, 6, 'Prefers'),
(54, 12, 'Disprefers'),
(40, 8, 'Disprefers'),
(56, 14, 'Prefers'),
(52, 4, 'Prefers'),
(39, 7, 'Disprefers'),
(45, 1, 'Prefers'),
(41, 7, 'Disprefers'),
(46, 6, 'Prefers'),
(44, 11, 'Disprefers'),
(55, 13, 'Prefers'),
(51, 15, 'Disprefers'),
(42, 2, 'Prefers'),
(48, 5, 'Disprefers'),
(49, 9, 'Prefers');

-- Inserting mock data into ACTIVITY
INSERT INTO ACTIVITY(subject_ID, room_ID, teacher_ID, day_time_ID, repetition, activity_type, duration) VALUES
(1, 1, 1, 1, 'everyWeek', 'Lecture', 2),
(2, 2, 2, 2, 'evenWeek', 'Tutorial', 2),
(3, 3, 1, 3, 'oddWeek', 'Lecture', 2),
(1, 1, 1, 5, 'everyWeek', 'Lecture', 2),
(7, 18, 51, 9, 'everyWeek', 'Seminar', 3),
(3, 13, 44, 14, 'oddWeek', 'Consultation', 3),
(11, 4, 42, 11, 'everyWeek', 'Exercise', 4),
(2, 9, 49, 2, 'evenWeek', 'Tutorial', 1),
(5, 16, 39, 7, 'oddWeek', 'Seminar', 3),
(8, 20, 53, 4, 'everyWeek', 'Demo', 4),
(12, 2, 48, 10, 'evenWeek', 'Exercise', 3),
(6, 14, 52, 8, 'oddWeek', 'Consultation', 4),
(1, 7, 40, 12, 'everyWeek', 'Seminar', 2),
(9, 17, 45, 5, 'evenWeek', 'Exam', 2),
(4, 11, 56, 15, 'oddWeek', 'Demo', 1),
(10, 5, 38, 6, 'everyWeek', 'Lecture', 4),
(13, 19, 50, 13, 'evenWeek', 'Tutorial', 1),
(14, 10, 41, 1, 'oddWeek', 'Lecture', 2),
(1, 8, 46, 16, 'everyWeek', 'Exam', 3);

-- One time Activity
INSERT INTO ACTIVITY(subject_ID, room_ID, teacher_ID, day_time_ID, repetition, activity_type, duration, activity_date) VALUES
(7, 1, 1, 13, 'oneTime', 'Lecture', 2, '2023-12-24'),
(8, 15, 45, 9, 'oneTime', 'Lecture', 2, '2024-1-26'),
(5, null, 39, null, 'oneTime', 'Exam', 3, '2024-1-27'),
(12, 3, 51, 4, 'oneTime', 'Consultation', 1, '2024-1-28'),
(2, null, 41, null, 'oneTime', 'Tutorial', 4, '2024-1-29');

-- Inserting mock data into PREFERED_SLOTS_ACTIVITY
INSERT INTO PREFERED_SLOTS_ACTIVITY(activity_ID, room_ID, teacher_ID, day_time_ID, preference) VALUES
(1, 1, 1, 1, 'Prefers'),
(1, 1, 1, 2, 'Prefers'),
(1, 1, 1, 3, 'Disprefers'),
(2, 2, 2, 2, 'Disprefers'),
(3, 3, 1, 3, 'Prefers'),
(15, 6, 54, 10, 'Prefers'),
(8, 16, 47, 3, 'Disprefers'),
(2, 9, 50, 12, 'Disprefers'),
(5, 11, 41, 8, 'Prefers'),
(12, 20, 55, 14, 'Disprefers'),
(18, 15, 52, 4, 'Disprefers'),
(9, 7, 43, 1, 'Prefers'),
(6, 2, 39, 7, 'Prefers'),
(11, 12, 46, 6, 'Prefers'),
(3, 19, 44, 11, 'Disprefers'),
(14, 10, 56, 13, 'Prefers'),
(17, 18, 51, 15, 'Disprefers'),
(10, 5, 42, 2, 'Prefers'),
(1, 17, 48, 5, 'Disprefers'),
(16, 1, 49, 9, 'Prefers');

-- Mock data for student's subjects
INSERT INTO STUDENT_ACTIVITIES(student_ID, activity_ID) VALUES
(5, 1),
(5, 2),
(5, 3),
(18, 19),
(10, 17),
(24, 8),
(14, 22),
(9, 12),
(20, 24),
(13, 3),
(8, 15),
(23, 9),
(12, 6),
(19, 16),
(22, 2),
(16, 12),
(11, 15),
(14, 4),
(18, 19),
(10, 17),
(24, 8),
(14, 22),
(9, 12),
(20, 24),
(13, 3),
(8, 15),
(23, 9),
(12, 6),
(19, 16),
(22, 2),
(16, 12),
(11, 15),
(14, 4);

INSERT INTO SUBJECT_TEACHER(user_ID,subject_ID) VALUES
(2, 2),
(3, 2),
(45, 49),
(9, 10),
(53, 7),
(49, 7),
(14, 8),
(41, 12),
(7, 11),
(53, 5),
(2, 4),
(50, 14),
(11, 3),
(39, 6),
(13, 2),
(56, 10),
(6, 13),
(46, 9),
(3, 12),
(38, 1);
