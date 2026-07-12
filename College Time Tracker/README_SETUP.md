# College Time Tracker (CTT) - PHP + MySQL/XAMPP Project

This folder contains a complete PHP final-year-project starter for **College Time Tracker**.

## 1) Save project in XAMPP

1. Open your XAMPP installation folder.
2. Open `htdocs`.
3. Copy the complete `ctt_php_project` folder into `htdocs`.
4. Final path should look like:
   - Windows: `C:\xampp\htdocs\ctt_php_project`
   - Browser URL: `http://localhost/ctt_php_project/`

## 2) Start services

1. Open XAMPP Control Panel.
2. Start **Apache**.
3. Start **MySQL**.

## 3) Create database in phpMyAdmin

1. Open `http://localhost/phpmyadmin`.
2. Click **New**.
3. Create database name: `ctt_db`.
4. Select `ctt_db`.
5. Click **Import**.
6. Choose file: `ctt_php_project/database/ctt_db.sql`.
7. Click **Go**.

## 4) Database connection file

Open `includes/config.php` and confirm these values:

- Host: `localhost`
- Username: `root`
- Password: empty string for default XAMPP
- Database: `ctt_db`

If your MySQL password is different, update it there.

## 5) Run project

Open:

`http://localhost/ctt_php_project/`

## 6) Default login accounts

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Academic In-Charge | `incharge` | `123456` |
| Teacher | `sajjad` | `123456` |
| Student | `ayesha` | `123456` |

## 7) Main features included

- Professional home page
- Login/logout
- Teacher/student signup
- Forgot password page UI
- Role-based dashboards
- Admin dashboard statistics
- User account management
- Academic entities management
- Timetable grid like the provided image
- Important dates column
- Period columns with normal and Friday timings
- Timetable print
- Timetable CSV export
- Conflict checking while adding timetable cells
- Teacher workload reports
- Room utilization reports
- Database backup download

## 8) File structure

```text
ctt_php_project/
  index.php
  login.php
  signup.php
  forgot_password.php
  logout.php
  includes/
    config.php
    db.php
    functions.php
    layout.php
  assets/
    css/style.css
    js/app.js
  admin/
    dashboard.php
    users.php
    entities.php
    timetable.php
    reports.php
    backup.php
  incharge/
    dashboard.php
    users.php
    timetable.php
    reports.php
  teacher/
    dashboard.php
  student/
    dashboard.php
  database/
    ctt_db.sql
```

## 9) Notes for final-year project viva

You can explain the project as a centralized academic scheduling system where Administrator, Academic In-Charge, Teacher, and Student use role-specific dashboards. Timetable conflict prevention checks teacher, room, period, and day overlaps before adding a lecture. The timetable grid can be printed or exported for academic office records.
