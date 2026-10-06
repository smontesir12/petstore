Project Context: PetStore Sales Management System
1. Project Overview
The ultimate goal of this project is to build a PetStore Sales Management System to simplify the inventory and sales management of pet foods and related products.

Current Phase Scope: This phase focuses strictly on establishing the foundational User Management Module. We are building a secure authentication system, user CRUD operations, and a statistics dashboard. Inventory and sales modules will be addressed in future phases.

2. Technology Stack
Backend: PHP 7.4+ (Procedural with PDO)
Database: MySQL / MariaDB
Server Environment: XAMPP / LAMP / WAMP
Database Management: phpMyAdmin
Frontend: HTML5, CSS3
3. Database Requirements
Database Name: petstore_db
Table: users
Schema:
id (INT, Primary Key, Auto-Increment)
username (VARCHAR, Unique)
email (VARCHAR, Unique)
password_hash (VARCHAR, for password_hash() storage)
is_active (TINYINT/BOOLEAN, 1 = active, 0 = inactive)
created_at (TIMESTAMP, default current time)
4. Security Requirements
Passwords must be hashed using password_hash().
Login verification must use password_verify().
All database queries must use PDO Prepared Statements to prevent SQL Injection.
Session-based authentication with secure logout (session destruction).
Strict input validation on both client and server sides.
5. File Structure
text

petstore_project/
├── config/
│   └── database.php       # DB connection using PDO
├── users/                 # User CRUD module
│   ├── index.php          # View all users
│   ├── create.php         # Add new user
│   ├── edit.php           # Edit existing user
│   └── delete.php         # Delete/deactivate user
├── assets/
│   └── css/
│       └── style.css      # Application styling
├── index.php              # Login page
├── dashboard.php          # Dashboard with stats
├── logout.php             # Session destruction
└── project_context.md     # This file
6. Deliverables Breakdown
Deliverable A: Create the Database
Create the MySQL database (petstore_db).
Create the users table with the specified schema.
Insert sample data (admin, user1, user2) for testing purposes.
Deliverable B: Login Implementation (From Scratch)
config/database.php: Secure database connection using PDO.
index.php: Login form (username, password) with error display.
Authentication logic: Validate credentials, start session, redirect to dashboard.
logout.php: Destroy session and redirect to login.
Deliverable C: User CRUD Implementation (From Scratch)
users/index.php: Display list of all users.
users/create.php: Form to add users with validation.
users/edit.php: Form to update existing user details.
users/delete.php: Logic to delete or deactivate a user.
Implement success/error flash messages.
Deliverable D: Dashboard Implementation (From Scratch)
dashboard.php: Protected page (redirect to login if not authenticated).
Display statistics: Total users, Active users, Inactive users.
Display current logged-in user's username.
Include navigation links (User Management, Logout).