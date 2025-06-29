CREATE DATABASE IF NOT EXISTS lab_booking_system;
USE lab_booking_system;

CREATE TABLE labs (
    lab_id INT AUTO_INCREMENT PRIMARY KEY,
    lab_name VARCHAR(100),
    location VARCHAR(100),
    lab_type VARCHAR(50),
    Lab_code VARCHAR(20),
    capacity INT,
    description TEXT,
    availability ENUM('Available', 'Unavailable'),
    status TEXT
);
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('Student', 'Instructor', 'Lab_TO', 'Lecturer','Admin')
);
CREATE TABLE students (
    stu_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    semester VARCHAR(20),
     Department VARCHAR(50),
     Password VARCHAR(50),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);
CREATE TABLE instructors (
    ins_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ins_name VARCHAR(100),
    ins_email VARCHAR(100),
        Department VARCHAR(50),
        Password VARCHAR(50),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);
CREATE TABLE lab_to (
    to_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);
CREATE TABLE lecturers (
    lec_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    department VARCHAR(100),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);
CREATE TABLE lab_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    lab_id INT,
    ins_id INT,
    booking_date DATE,
    purpose TEXT,
    status ENUM('Pending', 'Approved', 'Rejected'),
    FOREIGN KEY(lab_id) REFERENCES labs(lab_id),
    FOREIGN KEY(ins_id) REFERENCES instructors(ins_id)
);
CREATE TABLE lab_equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(100) NOT NULL,
    lab_id INT NOT NULL,
    student_id INT NOT NULL,
    usage_date DATE NOT NULL,
    `condition` VARCHAR(100),
    remarks TEXT
    FOREIGN KEY (lab_id) REFERENCES labs(lab_id),
  FOREIGN KEY (student_id) REFERENCES students(stu_id)
);

    CREATE TABLE Modules (
        Module_ID VARCHAR(10) PRIMARY KEY,
        Module_Name VARCHAR(100),
        Semester INT,
        credit INT
    );
    

CREATE TABLE usage_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    lab_id INT,
    student_id INT,
    date DATE,
    activity TEXT,
    FOREIGN KEY(lab_id) REFERENCES labs(lab_id),
    FOREIGN KEY(student_id) REFERENCES students(stu_id)
);