CREATE DATABASE lms_db;

USE lms_db;

CREATE TABLE Students (
    Student_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255),
    Student_ID_Number VARCHAR(255) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Course VARCHAR(255),
    Year_Level VARCHAR(255),
    Picture VARCHAR(255),
    Status ENUM('inside', 'outside') DEFAULT 'outside'
);

-- TIME IN AND TIME OUT OF STUDENTS
CREATE TABLE Student_Logs (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    Time_In DATETIME NOT NULL,
    Time_Out DATETIME DEFAULT NULL,
    FOREIGN KEY (Student_ID) REFERENCES Students(Student_ID)
);

CREATE TABLE Faculty (
    Faculty_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255),
    Student_ID_Number VARCHAR(255) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Department VARCHAR(255),
    Designation VARCHAR(255),
    Picture VARCHAR(255),
    Status ENUM('inside', 'outside') DEFAULT 'outside'
);

CREATE TABLE Book (
    Book_ID INT AUTO_INCREMENT PRIMARY KEY,
    ISBN VARCHAR(255) UNIQUE,
    Title VARCHAR(255),
    Cover_Image VARCHAR(255),
    Description VARCHAR(255),
    Author VARCHAR(255),
    Category VARCHAR(255),
    Publication_Date VARCHAR(255),
    Location VARCHAR(255),
    Total_Copies INT,
    Available_Copies INT,
    Borrowed_Copies INT
);

CREATE TABLE Borrow_Record (
    Borrow_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_Type ENUM('student','faculty') NOT NULL,
    Student_ID_Number VARCHAR(255) NOT NULL,
    Book_ID INT NOT NULL,
    Borrow_Date DATE NOT NULL,
    Due_Date DATE NOT NULL,
    Return_Date DATE,
    Fine DECIMAL(10,2) DEFAULT 0.00,
    Status ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    FOREIGN KEY (Book_ID) REFERENCES Book(Book_ID)
);

CREATE TABLE Category (
    Category_ID INT AUTO_INCREMENT PRIMARY KEY,
    Category_Name VARCHAR(255) UNIQUE
);

CREATE TABLE Location (
    Location_ID INT AUTO_INCREMENT PRIMARY KEY,
    Location_Name VARCHAR(255) UNIQUE
);

CREATE TABLE Librarian (
    Librarian_ID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255),
    Email VARCHAR(255) UNIQUE,
    Password VARCHAR(255)
);