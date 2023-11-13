USE xsluka00;

DROP TABLE IF EXISTS `USERS`;
DROP TABLE IF EXISTS `SUBJECTS`;
DROP TABLE IF EXISTS `ACTIVITY`;
DROP TABLE IF EXISTS `ROOM`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_TEACHER`;
DROP TABLE IF EXISTS `PREFERED_SLOTS_ACTIVITY`;
DROP TABLE IF EXISTS `DAY_TIME`;

CREATE TABLE USERS (
    /*PK*/
    user_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    username VARCHAR(250) NOT NULL UNIQUE,
    hashed_password VARCHAR(255) NOT NULL,  
    email VARCHAR(250) NOT NULL UNIQUE,
    user_role ENUM('Admin', 'Guarantor', 'Teacher', 'Scheduler', 'Student', 'Unregistered') NOT NULL
);
CREATE TABLE ROOM (
    /*PK*/
    room_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    room_name VARCHAR(50) NOT NULL,
    capacity VARCHAR(250) NOT NULL,
    room_location VARCHAR(250) NOT NULL
);
CREATE TABLE DAY_TIME (
    /*PK*/
    day_time_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*Attributes*/
    week_day ENUM('Pondelok', 'Utorok', 'Streda', 'Štvrtok','Piatok','Sobota','Ňedeľa')NOT NULL,
    time_range VARCHAR(250) NOT NULL
);
CREATE TABLE PREFERED_SLOTS_TEACHER (
    /*PK*/
    teacher_slot_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    guarantor_ID INT REFERENCES USER(user_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    preference ENUM('Preferuje', 'Nepreferuje')NOT NULL
);


CREATE TABLE SUBJECTS (
    /*PK*/
    subject_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    guarantor_ID INT REFERENCES USER(user_ID),
    /*Attributes*/
    title VARCHAR(50) NOT NULL,
    abbervation VARCHAR(250) NOT NULL,
    credits INT NOT NULL
);

CREATE TABLE ACTIVITY (
    /*PK*/
    activity_ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    /*FK*/
    subject_ID INT REFERENCES SUBJECTS(subject_ID),
    room_ID INT REFERENCES ROOM(room_ID),
    teacher_ID INT REFERENCES USERS(user_ID),
    preference_ID INT REFERENCES PREFERED_SLOTS_ACTIVITY(activity_slot_ID),
    day_time_ID INT REFERENCES DAY_TIME(day_time_ID),
    /*Attributes*/
    repetition ENUM ('každý', 'párny', 'nepárny', 'jednorázovo') NOT NULL,
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
    preference ENUM('Preferuje', 'Nepreferuje')NOT NULL
);

-- Inserting mock data into USER
INSERT INTO USERS(username, hashed_password, email, user_role) VALUES
('user1', '$2y$10$9mSWZYKjJW9YW9tgdYZa9uUuNVr9zhzcT0iOzruQo9w5KGqizrAv2', 'user1@email.com', 'Admin'),     /*password1*/
('user2', '$2y$10$KJYI.m9s/DRAtarK3SVD3efnyNygYdyjKFf1XoFNZdQEphb1/lLtG', 'user2@email.com', 'Guarantor'), /*password2*/
('user3', '$2y$10$iPVH5GDLM3YYMD5v53xrWu9qoNEyV11SzAzi4sWhJzAxQN/ZgPnqu', 'user3@email.com', 'Teacher'),   /*password3*/
('user4', '$2y$10$m3D3CAvaD9AjCla2qtnrNu4bHwFxn93ufVqBcFlTAUANzwMwVbeYG', 'user4@email.com', 'Scheduler'),  /*password4*/
('user5', '$2y$10$BjA2J9QWBIqO49t5JEi3n.0ihgNljwGN4ZrJyhFzdr/KkMVPBaie2', 'user5@email.com', 'Student'),  /*password5*/
('user6', '$2y$10$w9/9kGvCHssBMQUOUmJjyuOVYgOq4durbCIOXI6gpY3hz/4SGYswe', 'user6@email.com', 'Unregistered');  /*password6*/

-- Inserting mock data into ROOM
INSERT INTO ROOM(room_name, capacity, room_location) VALUES
('RoomA', '50', 'M216'),
('RoomB', '30', 'L216'),
('RoomC', '20', 'K303'),
('RoomD', '50', 'M210'),
('RoomE', '30', 'S210'),
('RoomF', '20', 'A110');

-- Inserting mock data into DAY_TIME
INSERT INTO DAY_TIME(week_day, time_range) VALUES
('Pondelok', '11:00-13:00'),
('Pondelok', '13:00-15:00'),
('Pondelok', '15:00-17:00'),
('Utorok', '9:00-11:00'),
('Utorok', '13:00-15:00'),
('Utorok', '15:00-17:00'),
('Streda', '9:00-11:00'),
('Streda', '15:00-17:00'),
('Štvrtok', '9:00-11:00'),
('Štvrtok', '11:00-13:00'),
('Štvrtok', '13:00-15:00'),
('Štvrtok', '15:00-17:00'),
('Piatok', '9:00-11:00'),
('Piatok', '11:00-13:00'),
('Piatok', '13:00-15:00'),
('Piatok', '15:00-17:00');

-- Inserting mock data into SUBJECTS
INSERT INTO SUBJECTS(guarantor_ID, title, abbervation, credits) VALUES
(1, 'Mathematics', 'MATH', 3),
(2, 'Physics', 'PHYS', 4),
(1, 'Chemistry', 'CHEM', 2);

-- Inserting mock data into PREFERED_SLOTS_TEACHER
INSERT INTO PREFERED_SLOTS_TEACHER(guarantor_ID, day_time_ID, preference) VALUES
(1, 1, 'Preferuje'),
(2, 2, 'Nepreferuje'),
(1, 3, 'Preferuje');

-- Inserting mock data into ACTIVITY
-- NOTE: This assumes an adjusted definition of ACTIVITY
INSERT INTO ACTIVITY(subject_ID, room_ID, teacher_ID, preference_ID, day_time_ID, repetition, activity_type) VALUES
(1, 1, 1, 1, 1, 'každý', 'Lecture'),
(2, 2, 2, 2, 2, 'párny', 'Tutorial'),
(3, 3, 1, 1, 3, 'nepárny', 'Lecture');

-- Inserting mock data into PREFERED_SLOTS_ACTIVITY
-- NOTE: Assuming some activity IDs from the ACTIVITY mock data
INSERT INTO PREFERED_SLOTS_ACTIVITY(activity_ID, room_ID, teacher_ID, day_time_ID, preference) VALUES
(1, 1, 1, 1, 'Preferuje'),
(2, 2, 2, 2, 'Nepreferuje'),
(3, 3, 1, 3, 'Preferuje');