# Student Feedback Management System

A secure **PHP & MySQL web application** that enables teachers to provide assessment feedback and allows students to view feedback and track their academic progress through a personalised dashboard.

This project was developed as part of my **final-year Computer Science dissertation** and demonstrates practical skills in web development, database design, authentication, and role-based access control.

---

## Screenshots

### Student Dashboard

![Student Dashboard](docs/student_dashboard.png)

Displays quiz results, feedback availability, risk indicators, and a progress chart.



![Progress Overview](docs/progress_overview.png)

Displays progress charts, score summaries, and score trends for the logged-in student.

### Teacher Dashboard

![Teacher Dashboard](docs/teacher_dashboard.png)

Displays student submissions, feedback actions, risk status, and class performance analytics.

### Feedback

![Enter Feedback](docs/enter_feedback.png)

Allows teachers to enter feedback for a selected student.

![Overwrite Feedback](docs/overwrite_feedback.png)

Allows teachers to overwrite previously submitted feedback for a selected student.

---

## Tech Stack

- **PHP**
- **MySQL**
- **HTML5**
- **CSS3**
- **JavaScript**
- **PDO** (secure database access)

---

## Features

- Student and teacher authentication
- Role-based access control
- Feedback management system
- Student progress dashboard
- Class performance overview
- Secure database queries using prepared statements
- Session-based authentication and route protection

---

## Project Structure

student-feedback-management-system/
├── include/
├── student/
├── teacher/
├── css/
├── docs/
├── index.php
├── logout.php
└── base_feedback_system.sql

---

## Local Setup

1. Create a MySQL database.
2. Import `base_feedback_system.sql`.
3. Update `include/db.php` with your database credentials.
4. Place the project inside your XAMPP/WAMP `htdocs` directory.
5. Start **Apache** and **MySQL**.
6. Open:

http://localhost/student-feedback-management-system

---

## What I Learned

Through this project I gained experience in:

- Building database-driven web applications
- Implementing secure authentication and session management
- Designing role-based user interfaces
- Writing secure SQL queries with PDO prepared statements
- Creating dashboard-style analytics using JavaScript charts
- Structuring a multi-user web application

---

## Security

Sensitive credentials have been removed from this repository. Database access uses PDO prepared statements to reduce the risk of SQL injection, and authenticated routes are protected through session-based access control.

---

## Possible Future Improvements

- Password reset functionality
- Export feedback reports to PDF
- Search and filtering features
- Email notifications
- Automated testing

---

## Author

Emirhan Gok
Computer Science Graduate

- GitHub: https://github.com/emirhan-gok
- LinkedIn: https://linkedin.com/in/emirhan-gok

---

## Notes

This repository demonstrates full-stack web development, database integration, secure authentication, role-based access control, and dashboard-based data presentation.
