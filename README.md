# Somalia NIRA - National Identification & Registration Authority System

## Project Description

The Somalia NIRA (National Identification & Registration Authority) System is a comprehensive digital platform designed to manage the national identification and registration process for citizens of Somalia. This system enables the government to issue secure national identification numbers (NIN), capture biometric data, and provide verification services for citizens' identities.

The NIRA system combines web-based interfaces with biometric technologies to create a robust national ID management solution. It allows for the registration of citizens, processing of ID applications, and verification of identities through both facial recognition and fingerprint biometrics.

## Features

### Citizen Registration
- User-friendly registration portal for citizens
- Collection of personal information (name, gender, date of birth, region, etc.)
- Document upload functionality (photo, birth certificate, passport, residency proof)
- Automatic generation of unique National Identification Numbers (NIN)
- Application status tracking

### Biometric Processing
- Facial recognition integration for identity verification
- Fingerprint capture and matching capabilities
- Secure storage of biometric templates
- Multi-modal biometric verification

### Admin Dashboard
- Comprehensive administrative interface
- Real-time statistics and analytics
- Application processing workflow
- Regional distribution visualization
- User management system

### Verification Services
- Public verification portal for ID validation
- API endpoints for third-party verification
- Verification logging and audit trail
- QR code-based verification option

### Security Features
- Secure authentication system
- Role-based access control
- Encrypted storage of sensitive data
- Activity logging and monitoring

## Setup Instructions

### Prerequisites
- XAMPP (Apache, MySQL, PHP) installed
- Python 3.7+ installed
- Web browser
- Internet connection for CDN resources

### Database Setup
1. Start XAMPP and ensure Apache and MySQL services are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `nira_system`
4. Import the database schema from `sql/nira_system.sql` by following these steps:
   - Click on the newly created `nira_system` database in phpMyAdmin
   - Click on the "Import" tab at the top
   - Click "Browse" and select the `sql/nira_system.sql` file from your project directory
   - Click "Go" at the bottom to import the schema and initial data
   - Note: This will automatically create the default admin user

### Web Application Setup
1. Clone or download the project to your XAMPP htdocs directory
2. Navigate to the project directory
3. Configure database connection in `config/database.php` if needed
4. Access the application through http://localhost/NIRA/public/

### Biometric Service Setup
1. Navigate to the biometrics directory: `cd biometrics`
2. Install required Python packages: `pip install -r requirements.txt`
3. Start the biometric service: `python app.py`
4. The service will run on http://localhost:5000 by default

### Fingerprint Service Setup
1. Ensure the fingerprint SDK is properly installed in the `fingerprint/sdk/` directory
2. The fingerprint service is automatically integrated with the web application

## Admin Login Information

### Default Admin Account
- Username: `admin`
- Password: `admin123`
- Role: Administrator

> **Important**: The default admin account is only created when you properly import the `sql/nira_system.sql` file into your database. If you're experiencing login issues, please ensure you've completed the database setup steps correctly.
>
> **Note**: If you're seeing an SQL error about duplicate entry for username 'admin', this means the admin user already exists in your database. You can proceed to login with the credentials above.

### User Roles
1. **Admin**: Full system access with user management capabilities
2. **Officer**: Registration and application processing access
3. **Verifier**: Limited access for ID verification only

### Admin Functions
- User Management: Create, edit, and deactivate admin accounts
- Application Processing: Review and approve/reject citizen applications
- System Configuration: Adjust system settings and parameters
- Reports Generation: Create and export system reports

## Troubleshooting

### Login Issues
1. **Invalid username or password error**
   - Ensure you've imported the `sql/nira_system.sql` file into your database
   - Verify the admin user exists in the `admins` table
   - If the admin user doesn't exist, you can manually add it using this SQL query:
     ```sql
     INSERT INTO admins (username, email, password_hash, role, full_name) VALUES 
     ('admin', 'admin@nira.gov.so', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');
     ```
   - If you see an error about duplicate entry for username 'admin', this means the admin user already exists

2. **Database connection issues**
   - Check that your database credentials in `config/database.php` match your MySQL setup
   - Ensure the MySQL service is running
   - Verify that the `nira_system` database exists

## API Documentation

The NIRA system provides API endpoints for integration with other systems:

- `/api/verify.php`: Verify citizen identity by NIN
- `/api/stats.php`: Get system statistics
- `/api/update-status.php`: Update application status

For detailed API documentation, please refer to the internal documentation.

INSERT INTO admins (username, email, password_hash, role, full_name) VALUES 
('admin', 'admin@nira.gov.so', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');INSERT INTO admins (username, email, password_hash, role, full_name) VALUES 
('admin', 'admin@nira.gov.so', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');## Troubleshooting

### Login Issues
1. **Invalid Username or Password Error**
   - Ensure you've properly imported the `sql/nira_system.sql` file into your database
   - Verify that the `admins` table contains the default admin user
   - If the admin user doesn't exist, run the following SQL query in phpMyAdmin:
     ```sql
     INSERT INTO admins (username, email, password_hash, role, full_name) VALUES 
     ('admin', 'admin@nira.gov.so', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');
     ```

2. **Database Connection Issues**
   - Check that your database credentials in `config/database.php` match your MySQL setup
   - Ensure MySQL service is running in XAMPP Control Panel
   - Verify that the `nira_system` database exists

## Support

For technical support or inquiries, please contact the system administrator.

---

© Somalia National Identification & Registration Authority