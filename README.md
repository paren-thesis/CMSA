# Church Management System (CMS)

A comprehensive church management system built with PHP, HTML, CSS, and MySQL for managing church members, attendance, and birthdays.

## 🚀 Features

- **🔐 Secure Authentication**: Admin login with session management and password hashing
- **👥 Member Management**: Add, edit, delete, and view church members with search/filter
- **📊 Attendance Tracking**: Create meetings, record attendance, and view detailed reports
- **🎂 Birthday Management**: Track upcoming birthdays, monthly views, and age calculations
- **📱 Responsive Design**: Mobile-friendly interface with modern UI
- **📈 Statistics Dashboard**: Overview of members, attendance, and birthdays
- **📤 Export Features**: Export data to CSV and print reports

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Vanilla JS for DOM manipulation and interactivity

## 📁 Project Structure

```
CMSA/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet with modern design
│   └── js/
│       └── main.js            # JavaScript functionality
├── config/
│   └── database.php           # Database connection and helper functions
├── database/
│   └── schema.sql             # MySQL database schema with sample data
├── includes/
│   ├── functions.php          # Utility functions and session management
│   └── navbar.php             # Shared navigation component
├── pages/
│   ├── members/               # Member management pages
│   ├── attendance/            # Attendance tracking pages
│   └── birthdays/             # Birthday management pages
├── index.php                  # Redirects to login
├── login.php                  # Admin authentication
├── logout.php                 # Session logout
├── dashboard.php              # Main dashboard with statistics
├── members.php                # Member listing and management
├── add_member.php             # Add new member form
├── edit_member.php            # Edit member information
├── view_member.php            # Detailed member view with attendance history
├── attendance.php             # Meeting and attendance management
├── view_attendance.php        # Detailed attendance reports
├── birthdays.php              # Birthday tracking and management
├── test_system.php            # System diagnostics and testing
├── fix_admin_password.php     # Admin password reset utility
└── README.md                  # This file
```

## 🚀 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or XAMPP/WAMP

### Step 1: Clone/Download the Project

```bash
git clone <repository-url>
cd CMSA
```

### Step 2: Database Setup

1. Create a MySQL database named `church_management_system`
2. Import the database schema:

```bash
mysql -u root -p church_management_system < database/schema.sql
```

Or use phpMyAdmin to import the `database/schema.sql` file.

### Step 3: Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'church_management_system');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 4: Web Server Configuration

#### Option A: Using XAMPP/WAMP
1. Copy the project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Start Apache and MySQL services
3. Access via `http://localhost/CMSA`

#### Option B: Using Built-in PHP Server
```bash
php -S localhost:8000
```
Then access via `http://localhost:8000`

### Step 5: Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **Important**: Change the default password after first login!

### Step 6: System Testing

Run the system test to verify everything is working:
```
http://localhost/cms/test_system.php
```

## 📊 Database Schema

### Tables Overview

1. **admins** - Admin user credentials with password hashing
2. **members** - Church member information (name, location, contact, DOB)
3. **meetings** - Meeting details and types (Sunday Service, Bible Study, etc.)
4. **attendance** - Attendance records linking members to meetings

### Sample Data

The system comes with sample data including:
- 1 admin user (admin/admin123)
- 5 sample members with birthdays
- 5 sample meetings
- Sample attendance records

## 🎯 Usage Guide

### 1. Authentication
- Access the login page at `login.php`
- Use default credentials or your custom admin account
- Secure session-based authentication with automatic logout

### 2. Dashboard
- View system overview with key statistics
- See total members, recent attendance, upcoming birthdays
- Quick access to all major functions
- Real-time data updates

### 3. Member Management
- **List Members**: View all members with search and filter options
- **Add Members**: Complete registration form with validation
- **Edit Members**: Update member information
- **Delete Members**: Remove members with confirmation
- **View Details**: See member profile with attendance history
- **Export Data**: Download member lists as CSV

### 4. Attendance Tracking
- **Create Meetings**: Add new meetings with date, type, and topic
- **Record Attendance**: Mark members present/absent for each meeting
- **View Reports**: Detailed attendance breakdowns
- **Filter Meetings**: By date or meeting type
- **Export Reports**: Print or download attendance data

### 5. Birthday Management
- **Upcoming Birthdays**: View next 30 days of birthdays
- **Monthly View**: Browse birthdays by month with calendar display
- **All Birthdays**: Complete birthday list sorted by date
- **Age Calculations**: Automatic age and countdown timers
- **Contact Integration**: Phone/email links for birthday wishes
- **Export Lists**: Download birthday data

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` function
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Security**: Secure session configuration with regeneration
- **Input Sanitization**: All user inputs are sanitized
- **Authentication Required**: All pages protected except login
- **CSRF Protection**: Session-based token validation

## 🎨 User Interface

### Modern Design
- **Bootstrap 5**: Responsive framework
- **Custom CSS**: Modern gradient design with hover effects
- **Mobile-Friendly**: Works on all device sizes
- **Interactive Elements**: Modals, tooltips, and dynamic content

### Navigation
- **Shared Navbar**: Consistent navigation across all pages
- **Breadcrumbs**: Clear page hierarchy
- **Quick Actions**: Easy access to common functions

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Login Issues**
   - Run `fix_admin_password.php` to reset admin password
   - Check if admin user exists in database
   - Verify session configuration

3. **Page Not Found (404)**
   - Check web server configuration
   - Verify file permissions
   - Ensure .htaccess is properly configured

4. **Session Issues**
   - Check PHP session configuration
   - Verify write permissions for session directory

### Debug Mode

To enable debug mode, add this to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### System Testing

Use the built-in system test:
```
http://localhost/cms/test_system.php
```

This will check:
- PHP environment
- File structure
- Database connection
- Function availability
- Navigation links

## 🔄 Updates & Maintenance

### Regular Maintenance
- Backup database regularly
- Update PHP and MySQL versions
- Monitor error logs
- Review and update security settings

### Adding New Features
1. Create new database tables if needed
2. Add functions to `includes/functions.php`
3. Create new page files in appropriate directories
4. Update navigation menu
5. Test thoroughly

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the code comments
- Run the system test for diagnostics

## 🎉 Acknowledgments

- Bootstrap for the responsive framework
- PHP community for best practices
- MySQL documentation for database optimization
- Font Awesome for icons

## 📋 Changelog

### Version 1.0.0 (Current)
- ✅ Complete authentication system
- ✅ Member management (CRUD operations)
- ✅ Attendance tracking with detailed reports
- ✅ Birthday management with multiple views
- ✅ Responsive design and modern UI
- ✅ Export and print functionality
- ✅ Security features and input validation
- ✅ System testing and diagnostics

---

**Happy Coding! 🚀** 