# Article Management System

A web-based content management system for managing articles, categories, and user comments. Built with PHP and MySQL, featuring a responsive Bootstrap interface.

## Features

- **User Authentication**
  - Secure login system
  - Role-based access control (Admin and User roles)
  - Profile management

- **Article Management**
  - Create, edit, and delete articles
  - Category organization
  - Image upload support
  - Rich text editor

- **Comment System**
  - User comments on articles
  - Comment moderation by admins
  - Comment approval workflow

- **Category Management**
  - Organize articles by categories
  - Category CRUD operations

- **Responsive Design**
  - Bootstrap 5 interface
  - Mobile-friendly layout
  - Modern UI/UX

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/XAMPP web server
- Modern web browser

## Installation

1. Clone or download the repository to your web server directory:
   ```
   git clone [repository-url]
   ```

2. Import the database:
   - Create a new MySQL database
   - Import the `artikel_db.sql` file

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     $host = 'localhost';
     $username = 'your_username';
     $password = 'your_password';
     $database = 'your_database';
     ```

4. Set up the web server:
   - Ensure the project is in your web server's root directory
   - Configure necessary permissions for the `assets/images/uploads` directory

## Usage

### Admin Panel

1. Access the admin panel at `/admin/login.php`
2. Log in with admin credentials
3. Manage articles, categories, and comments
4. Monitor dashboard statistics

### User Features

1. Register/Login at `/login.php`
2. Browse articles and categories
3. Leave comments on articles
4. Manage personal profile

## Directory Structure

```
/
├── admin/           # Admin panel files
├── assets/          # Static resources
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── images/      # Uploaded images
├── config/          # Configuration files
└── artikel_db.sql   # Database schema
```

## Security

- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Password hashing

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please open an issue in the repository or contact the system administrator.