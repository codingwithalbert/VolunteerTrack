🤝 VolunteerTrack - Hour Logging & Management System

A comprehensive web-based volunteer management system designed for non-profit organizations and community groups to efficiently track volunteer contributions, manage activities, and demonstrate impact.

📋 Table of Contents

Features
Demo Accounts
Technology Stack
Installation
Project Structure
User Roles
Security Features
Screenshots
Troubleshooting
Contributing
License


✨ Features
🎯 Core Functionality

User Management - Create and manage volunteers, coordinators, and administrators
Activity Tracking - Create and organize volunteer activities with descriptions and locations
Hour Logging - Volunteers log their hours with automatic verification workflow
Role-Based Access - Three distinct user roles with appropriate permissions
Dashboard Analytics - Real-time statistics and insights for each role
Password Reset - Secure email-based password reset system
Modern UI - Beautiful, responsive design with smooth animations

👤 Volunteer Features

✅ Log volunteer hours with activity selection
✅ View personal hours history
✅ Track verified, pending, and rejected hours
✅ Edit pending hour entries
✅ Personal statistics dashboard
✅ Profile management

👨‍💼 Coordinator Features

✅ Create and manage activities
✅ View all volunteer hour submissions
✅ Verify or reject pending hours
✅ Add hours on behalf of volunteers
✅ Monitor volunteer contributions
✅ Dashboard with pending approvals

👨‍💻 Administrator Features

✅ Full user management (CRUD operations)
✅ Activity management
✅ Hour verification and management
✅ System-wide analytics
✅ Role assignment
✅ User status control (active/inactive)


🔑 Demo Accounts
After installation, use these credentials to explore the system:
RoleUsernamePasswordAccess LevelAdministratoradminadmin123Full system accessCoordinatorcoordinator1coord123Activities & hour managementVolunteervolunteer1volunteer123Personal hour logging

⚠️ Important: Change these passwords immediately in production!


🛠️ Technology Stack
Backend

PHP 7.4+ - Server-side programming
MySQL 5.7+ - Database management
PDO - Database abstraction layer

Frontend

HTML5 - Structure
CSS3 - Modern styling with gradients and animations
JavaScript - Interactive features
Google Fonts (Inter) - Typography

Security

bcrypt - Password hashing
PDO Prepared Statements - SQL injection prevention
Session Management - User authentication
CSRF Protection - Form security
XSS Prevention - Output sanitization

👥 User Roles
🔴 Administrator
Full system access with all permissions
Can:

Manage all users (create, edit, delete)
Manage all activities
Verify/reject all hour submissions
Access system-wide analytics
Change user roles and status
View all dashboards

Dashboard Shows:

Total volunteers count
Active activities count
Total verified hours
Pending approvals count
Recent hour submissions


🟡 Coordinator
Activity and hour management access
Can:

Create and manage activities
View all volunteer hour submissions
Verify or reject hours
Add hours for volunteers
Monitor volunteer contributions
Access coordinator dashboard

Cannot:

Manage users
Delete users
Change user roles

Dashboard Shows:

Volunteer statistics
Activity overview
Pending verifications with quick actions
Recent submissions


🟢 Volunteer
Personal hour tracking access
Can:

Log volunteer hours
View personal hours history
Edit pending hour entries
View activity details
Manage personal profile
Access volunteer dashboard

Cannot:

Create activities
Verify hours
View other volunteers' data
Access admin/coordinator features

Dashboard Shows:

Personal verified hours
Pending hours
Total entries
Available activities
Recent submissions with status

🔒 Security Features
Authentication & Authorization

✅ Session-based authentication - Secure user sessions
✅ Role-based access control - Permission management
✅ Session timeout - Auto-logout after 1 hour of inactivity
✅ Password requirements - Minimum 6 characters

Password Security

✅ bcrypt hashing - Industry-standard password hashing
✅ Salted passwords - Protection against rainbow tables
✅ Secure reset tokens - 64-character random tokens
✅ Token expiration - Reset links expire after 1 hour
✅ One-time use tokens - Tokens can't be reused

Database Security

✅ PDO prepared statements - SQL injection prevention
✅ Parameter binding - Safe data handling
✅ Foreign key constraints - Data integrity
✅ Proper indexing - Performance optimization

Application Security

✅ XSS prevention - htmlspecialchars() on all output
✅ CSRF protection - Session validation
✅ Input validation - Server-side validation
✅ Error handling - Secure error messages
✅ File upload restrictions - N/A (no file uploads)