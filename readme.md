# Academic Record System

A web-based student information management system for high school teachers.

## Features
- Grade entry (10 Written Works, 10 Performance Tasks, 2 Quarterly Assessments)
- 3 semesters
- Report card generation with search
- Teacher-to-section assignment
- CSV student import
- Audit log for grade changes

## Requirements
- XAMPP (Apache, MySQL, PHP)
- PHP 7.4+

## Installation
1. Clone repository to `C:\xampp\htdocs\`
2. Copy `config/database.sample.php` to `config/database.php`
3. Import `database.sql` to phpMyAdmin
4. Login with `admin` / `admin123`

## Default Accounts
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Subject Teacher | math_teacher | admin123 |
| Advisory Teacher | advisory_teacher | admin123 |