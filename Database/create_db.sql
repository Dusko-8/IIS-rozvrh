USE iis;

DROP TABLE IF EXISTS `USERS`;
DROP TABLE IF EXISTS `SUBJECTS`;
DROP TABLE IF EXISTS `ACTIVITY`;
DROP TABLE IF EXISTS `ROOM`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_TEACHER`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_ACTIVITY`;
DROP TABLE IF EXISTS `DAY_TIME`;
DROP TABLE IF EXISTS `STUDENT_ACTIVITIES`;

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
    guarantor_ID INT REFERENCES USER(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    preference ENUM('Prefers', 'Disprefers')NOT NULL
);

CREATE TABLE STUDENT_ACTIVITIES(
    /*PK*/
    student_subjects_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    student_ID INT REFERENCES USER(user_ID),
    activity_ID int REFERENCES ACTIVITY(activity_ID)
);

CREATE TABLE SUBJECTS (
    /*PK*/
    subject_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    guarantor_ID INT REFERENCES USER(user_ID),
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
    activity_type VARCHAR(150) NOT NULL
);

CREATE TABLE PREFERED_SLOTS_ACTIVITY (
    /*PK*/
    activity_slot_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    activity_ID INT REFERENCES ACTIVITY(activity_ID),
    room_ID INT REFERENCES ROOM(room_ID),
    teacher_ID INT REFERENCES USER(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    preference ENUM('Prefers', 'Disprefers')NOT NULL
);

-- Inserting mock data into USER
INSERT INTO USERS(username, hashed_password, email, user_role) VALUES
('Admin', '$2y$10$9mSWZYKjJW9YW9tgdYZa9uUuNVr9zhzcT0iOzruQo9w5KGqizrAv2', 'user1@email.com', 'Admin'),     /*password1*/
('Guarantor', '$2y$10$KJYI.m9s/DRAtarK3SVD3efnyNygYdyjKFf1XoFNZdQEphb1/lLtG', 'user2@email.com', 'Guarantor'), /*password2*/
('Teacher', '$2y$10$iPVH5GDLM3YYMD5v53xrWu9qoNEyV11SzAzi4sWhJzAxQN/ZgPnqu', 'user3@email.com', 'Teacher'),   /*password3*/
('Scheduler', '$2y$10$m3D3CAvaD9AjCla2qtnrNu4bHwFxn93ufVqBcFlTAUANzwMwVbeYG', 'user4@email.com', 'Scheduler'),  /*password4*/
('Student', '$2y$10$BjA2J9QWBIqO49t5JEi3n.0ihgNljwGN4ZrJyhFzdr/KkMVPBaie2', 'user5@email.com', 'Student'),  /*password5*/


-- Inserting mock data into ROOM
INSERT INTO ROOM (room_name, capacity, room_location) VALUES
('RoomA', 50, 'M216'),
('RoomB', 30, 'L216'),
('RoomC', 20, 'K303'),
('RoomD', 50, 'M210'),
('RoomE', 30, 'S210'),
('RoomF', 20, 'A110');


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
(1, 'Mathematics', 'MATH', 3, 'Fundamental course covering algebra, calculus, and geometry'),
(2, 'Physics', 'PHYS', 4, 'Comprehensive study of matter, energy, and motion'),
(3, 'Biology', 'BIOL', 4, 'Exploration of living organisms and life processes'),
(4, 'History', 'HIST', 3, 'In-depth analysis of historical events and periods'),
(5, 'English Literature', 'ENGL', 3, 'Study of classic and contemporary literary works'),
(6, 'Computer Science', 'COMP', 5, 'Course on programming, algorithms, and system design'),
(7, 'Art History', 'ARTH', 2, 'Survey of art movements and key artists through history'),
(8, 'Economics', 'ECON', 3, 'Understanding economic theories, models, and applications'),
(9, 'Psychology', 'PSYC', 3, 'Introduction to mental processes and behavior studies'),
(10, 'Chemistry', 'CHEM', 2, 'Introduction to chemical reactions and compounds');
-- Inserting mock data into PREFERED_SLOTS_TEACHER
INSERT INTO PREFERED_SLOTS_TEACHER(guarantor_ID, day_time_ID, preference) VALUES
(1, 1, 'Prefers'),
(2, 2, 'Disprefers'),
(1, 3, 'Prefers');

-- Inserting mock data into ACTIVITY
-- NOTE: This assumes an adjusted definition of ACTIVITY
INSERT INTO ACTIVITY(subject_ID, room_ID, teacher_ID, day_time_ID, repetition, activity_type, duration) VALUES
(1, 1, 1, 1, 'everyWeek', 'Lecture', 2),
(2, 2, 2, 2, 'evenWeek', 'Tutorial', 2),
(3, 3, 1, 3, 'oddWeek', 'Lecture', 2),
(1, 1, 1, 5, 'everyWeek', 'Lecture', 2);
-- One time Activity
INSERT INTO ACTIVITY(subject_ID, room_ID, teacher_ID, day_time_ID, repetition, activity_type, duration, activity_date) VALUES
(7, 1, 1, 13, 'oneTime', 'Lecture', 2, '2023-11-24');

-- Inserting mock data into PREFERED_SLOTS_ACTIVITY
-- NOTE: Assuming some activity IDs from the ACTIVITY mock data
INSERT INTO PREFERED_SLOTS_ACTIVITY(activity_ID, room_ID, teacher_ID, day_time_ID, preference) VALUES
(1, 1, 1, 1, 'Prefers'),
(1, 1, 1, 2, 'Prefers'),
(1, 1, 1, 3, 'Disprefers'),
(2, 2, 2, 2, 'Disprefers'),
(3, 3, 1, 3, 'Prefers');

-- Mock data for student's subjects
INSERT INTO STUDENT_ACTIVITIES(student_ID, activity_ID) VALUES
(5, 1),
(5, 2),
(5, 3);