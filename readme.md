# ReNew Notebooks

A server-side web application for ReNew Ltd., a company selling refurbished notebooks.

## Overview

This application provides a full-featured web platform for browsing and managing refurbished notebooks. It includes a multi-level menu system, user authentication, SOAP and RESTful web services, and PDF generation capabilities.

## Features

- Responsive design with Bootstrap
- Multi-level menu system stored in the database
- User authentication with three role levels (visitor, registered, admin)
- Comprehensive notebook catalog with filtering and pagination
- SOAP web service for accessing notebook data
- Integration with Hungarian National Bank SOAP service for currency exchange rates
- RESTful API with CRUD operations
- PDF report generation using TCPDF
- Admin panel for content management

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP extensions: mysqli, soap, json, mbstring

## Installation

1. Clone the repository to your web server's document root:
   ```
   git clone https://github.com/yourusername/renew-notebooks.git
   ```

2. Create a MySQL database:
   ```sql
   CREATE DATABASE renew_notebooks;
   ```

3. Import the database structure:
   ```
   mysql -u username -p renew_notebooks < database/structure.sql
   ```

4. Configure the database connection in `config/db_config.php`:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'renew_notebooks');
   ```

5. Import sample data:
   - Navigate to `http://your-server/renew-notebooks/import_processors.php`
   - Navigate to `http://your-server/renew-notebooks/import_opsystems.php`
   - Navigate to `http://your-server/renew-notebooks/import_notebooks.php`

6. Create an admin user by registering at:
   ```
   http://your-server/renew-notebooks/auth/register.php
   ```
   Then update the user role to 'admin' in the database:
   ```sql
   UPDATE users SET role = 'admin' WHERE username = 'your_username';
   ```

## Project Structure

```
renew-notebooks/
├── admin/                  # Administration panel
├── assets/                 # CSS, JS, images
├── auth/                   # Authentication files
├── config/                 # Configuration files
├── includes/               # Common includes
├── services/               # API endpoints
├── import_*.php            # Data import scripts
└── index.php               # Main entry point
```

## Web Services

### SOAP Service

The SOAP service is available at:
```
http://your-server/renew-notebooks/services/soap_service.php
```

Test it using the SOAP client at:
```
http://your-server/renew-notebooks/admin/soap_client.php
```

### RESTful API

The RESTful API is available at:
```
http://your-server/renew-notebooks/services/rest_service.php
```

Test it using the REST client at:
```
http://your-server/renew-notebooks/admin/rest_client.php
```

### MNB Exchange Rate Service

Access currency exchange rates at:
```
http://your-server/renew-notebooks/mnb_service.php
```

## PDF Generation

Generate PDF reports by visiting:
```
http://your-server/renew-notebooks/pdf_generator.php
```

## License

[MIT License]

## Contact

[Newfoundland]