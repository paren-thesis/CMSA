# Church Management System – Abstract (PHP, HTML, CSS)

## 🖥️ System Overview

A simple yet secure Church Management System (CMS) built with **PHP**, **HTML**, and **CSS** that allows administrators to:

-   ✅ Manage church members (add, edit, delete, view)
-   ✅ Track weekly attendance (record and view meeting participation)
-   ✅ Monitor birthdays (upcoming birthdays dashboard)
-   ✅ Secure admin login (authentication and session management)

---

## 🧱 Technology Stack

### 1. Frontend (Client-Side)

-   HTML5 (Structure)
-   CSS3 (Styling with Flexbox/Grid for responsive design)
-   Vanilla JavaScript (Basic DOM manipulation)
-   Bootstrap CSS (Optional: For faster UI development)

### 2. Backend (Server-Side)

-   PHP (Core logic, database interactions, authentication)
-   MySQL (Database for storing members, attendance, and admin credentials)
-   Sessions & Cookies (User authentication and security)

### 3. Security Considerations

-   Password hashing (`password_hash()` and `password_verify()`)
-   SQL Injection prevention (Prepared statements with `mysqli` or `PDO`)
-   Session protection (Regenerate session ID on login)

---

## ✨ Key Features

### 1. Admin Authentication

-   Login/Logout system (Secure session-based authentication)
-   Password recovery (Optional: Email-based reset)

### 2. Member Management

-   Add new members (Name, location, contact, DoB, program)
-   Edit/Delete members (Update records or remove entries)
-   Search & Filter (Find members by name, location, or program)

### 3. Attendance Tracking

-   Record weekly meetings (Date, meeting type, attendees)
-   View attendance history (Filter by date or member)
-   Export reports (CSV/PDF optional feature)

### 4. Birthday Tracker

-   Upcoming birthdays (Dashboard showing next 30 days)
-   Monthly birthday list (Filter by month)
-   Birthday reminders (Optional: Email notifications)

---

## 🗄️ Database Structure (MySQL)

### 1. `members` Table

-   `id` (Primary Key)
-   `first_name`, `last_name`
-   `location`, `program_of_study`
-   `contact_number`, `email`
-   `date_of_birth` (For birthday tracking)
-   `join_date`

### 2. `meetings` Table

-   `id` (Primary Key)
-   `meeting_date`, `meeting_type` (e.g., Sunday Service, Bible Study)
-   `topic` (Optional)

### 3. `attendance` Table

-   `id` (Primary Key)
-   `member_id` (Foreign Key → `members.id`)
-   `meeting_id` (Foreign Key → `meetings.id`)
-   `attended` (Boolean: 1 for present, 0 for absent)

### 4. `admins` Table

-   `id` (Primary Key)
-   `username`, `email`
-   `password_hash` (Securely hashed)

---

## 🧭 User Interface (UI) Flow

1. **Login Page** → Secure admin access
2. **Dashboard** → Overview of members, recent attendance, upcoming birthdays
3. **Members Section** → Add, edit, delete, search members
4. **Attendance Section** → Record and view meeting attendance
5. **Birthdays Section** → View upcoming and monthly birthdays

---

## 🚀 Deployment Options

-   Shared Hosting (cPanel, Apache, PHP/MySQL support)
-   VPS (More control, e.g., DigitalOcean, Linode)
-   Local Development (XAMPP/WAMP for testing)

---

## 🔮 Future Enhancements (Optional)

-   Email notifications (Birthday reminders, attendance reports)
-   Mobile-friendly UI (Better responsiveness)
-   Donation tracking (If needed for church finances)

---

## 📌 Summary

This PHP-based Church Management System provides a secure, easy-to-use interface for admins to track members, attendance, and birthdays without complex frameworks. The system is lightweight, database-driven, and customizable for different church needs.
