# Church Management System (CMS)

A comprehensive church management system built with PHP, HTML, CSS, and MySQL for managing church members, attendance, and birthdays.

## 🚀 Features

- **Member Management**: Add, edit, delete, and view church members
- **Attendance Tracking**: Record and monitor weekly meeting attendance
- **Birthday Management**: Track upcoming birthdays and monthly birthday lists
- **Secure Authentication**: Admin login with session management
- **Responsive Design**: Mobile-friendly interface
- **Modern UI**: Clean and intuitive user interface

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Vanilla JS for DOM manipulation

## 📁 Project Structure

```
CMSA/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   └── functions.php
├── pages/
│   ├── members/
│   ├── attendance/
│   └── birthdays/
├── index.php
├── login.php
├── dashboard.php
└── README.md
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

## 📊 Database Schema

### Tables Overview

1. **admins** - Admin user credentials
2. **members** - Church member information
3. **meetings** - Meeting details and types
4. **attendance** - Attendance records (junction table)

### Sample Data

The system comes with sample data including:
- 5 sample members
- 5 sample meetings
- Sample attendance records

## 🎯 Usage Guide

### 1. Login
- Access the login page
- Use default credentials or your custom admin account

### 2. Dashboard
- View system overview
- See total members count
- Check recent attendance
- View upcoming birthdays

### 3. Member Management
- Add new members with complete information
- Edit existing member details
- Delete members (with confirmation)
- Search and filter members

### 4. Attendance Tracking
- Record attendance for meetings
- View attendance history
- Filter by date or member

### 5. Birthday Management
- View upcoming birthdays (next 30 days)
- Check monthly birthday lists
- Track member ages

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` function
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Secure session configuration
- **Input Sanitization**: All user inputs are sanitized
- **CSRF Protection**: Session-based token validation

## 🎨 Customization

### Styling
- Edit `assets/css/style.css` for custom styling
- Modify color scheme in CSS variables
- Add custom Bootstrap overrides

### Functionality
- Extend functions in `includes/functions.php`
- Add new features in respective page files
- Modify database queries as needed

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

2. **Page Not Found (404)**
   - Check web server configuration
   - Verify file permissions
   - Ensure .htaccess is properly configured

3. **Session Issues**
   - Check PHP session configuration
   - Verify write permissions for session directory

### Debug Mode

To enable debug mode, add this to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

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

## 🎉 Acknowledgments

- Bootstrap for the responsive framework
- PHP community for best practices
- MySQL documentation for database optimization

---

**Happy Coding! 🚀** 