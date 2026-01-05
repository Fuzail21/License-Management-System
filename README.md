# License Management System

A comprehensive Laravel-based license management system for tracking software licenses, employees, departments, and vendors.

## Features

- **Employee Management**: Complete CRUD operations for employee records (independent of user accounts)
- **License Management**: Track software licenses with renewal cycles and costs
- **Department Management**: Organize employees by departments
- **Vendor Management**: Manage software vendors and their products
- **License Assignment**: Assign licenses to employees with status tracking
- **License Renewals**: Track license renewal history and costs
- **Dashboard Analytics**: Real-time statistics and recent license assignments
- **User Feedback System**: Collect and manage user feedback
- **Role-Based Authentication**: Secure admin panel with authentication

## Architecture

### Clean Separation of Concerns
The system implements a clean architecture separating authentication from business logic:

- **User Model**: Used exclusively for authentication (login, register, logout)
- **Employee Model**: Used for all business logic and license management
- **No Foreign Key**: Employees are independent entities, not tied to user accounts
- **Email Lookup**: Authenticated users are linked to employee records via email matching

### Tech Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: SQL Server (Azure SQL compatible)
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **Build Tool**: Vite

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQL Server (or Azure SQL Database)
- SQL Server PHP Extensions (sqlsrv, pdo_sqlsrv)

### Local Development Setup

1. **Clone the repository**
```bash
git clone <repository-url>
cd license-management-system
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure Database** (Edit `.env`)
```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=license_mangement_system
DB_USERNAME=sa
DB_PASSWORD=your-password
DB_TRUST_SERVER_CERTIFICATE=true
```

6. **Run Migrations**
```bash
php artisan migrate
```

7. **Create Storage Link**
```bash
php artisan storage:link
```

8. **Build Frontend Assets**
```bash
npm run dev
# OR for production
npm run build
```

9. **Start Development Server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Database Schema

### Core Tables
- `users` - Authentication accounts
- `employees` - Business entity records
- `departments` - Organizational departments
- `vendors` - Software vendors
- `licenses` - Software licenses
- `user_licenses` - License assignments to employees
- `license_renewals` - Renewal history tracking
- `user_feedback` - User feedback records
- `settings` - Application settings

### Key Relationships
- Employee → Department (many-to-one, nullable)
- UserLicense → Employee (many-to-one)
- UserLicense → License (many-to-one)
- License → Vendor (many-to-one)
- Department → Employees (one-to-many)

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions for Azure App Service.

### Quick Deployment Checklist
1. Configure production environment variables
2. Set up Azure SQL Database
3. Run migrations on production
4. Build frontend assets
5. Configure storage permissions
6. Enable HTTPS/SSL
7. Test authentication and core features

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── EmployeeController.php      # Employee CRUD
│   │   │   ├── DashboardController.php     # Dashboard
│   │   │   ├── UserLicenseController.php   # License assignments
│   │   │   ├── DepartmentController.php    # Department management
│   │   │   └── ...
│   │   └── Auth/
│   │       └── AuthController.php          # Authentication
│   └── Requests/
│       └── Admin/
│           ├── StoreEmployeeRequest.php    # Employee validation
│           └── UpdateEmployeeRequest.php
├── Models/
│   ├── User.php                            # Authentication model
│   ├── Employee.php                        # Business logic model
│   ├── Department.php
│   ├── License.php
│   ├── UserLicense.php
│   └── ...
database/
├── migrations/
│   ├── 2025_12_31_141722_create_employees_table.php
│   ├── 2025_12_31_142128_add_employee_id_to_user_licenses_table.php
│   ├── 2025_12_31_143047_migrate_users_data_to_employees_table.php
│   └── ...
resources/
├── views/
│   ├── admin/
│   │   ├── employees/         # Employee views
│   │   ├── departments/       # Department views
│   │   ├── licenses/          # License views
│   │   ├── user-licenses/     # Assignment views
│   │   └── dashboard.blade.php
│   ├── auth/                  # Authentication views
│   └── layouts/
```

## Key Features Explained

### Employee Management
- Create employees without requiring login accounts
- Assign employees to departments (optional)
- Track employee status (active/inactive)
- Mark employees as department heads
- Independent from authentication system

### License Assignment
- Assign licenses to employees
- Track assignment dates and expiry
- Monitor license status (active/expired/suspended)
- View assignment history
- Link to employee and department records

### Dashboard
- Real-time statistics (employees, departments, vendors, licenses)
- Active vs expired license counts
- Recent license assignments with employee details
- Quick access to management functions

### Authentication
- Secure login/register system
- Rate limiting for brute force protection
- User status validation
- Session management
- Logout functionality

## Testing

The system has been thoroughly tested through 5 phases:

- ✅ Phase 1: Employee Model Setup
- ✅ Phase 2: Data Migration
- ✅ Phase 3: Controller Refactoring
- ✅ Phase 4: View Updates
- ✅ Phase 5: Integration Testing

See [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) for detailed testing results.

## API Endpoints

### Admin Routes (Require Authentication)
```
GET    /admin/dashboard              - Dashboard
GET    /admin/employees              - List employees
POST   /admin/employees              - Create employee
GET    /admin/employees/create       - Create form
GET    /admin/employees/{employee}   - View employee
PUT    /admin/employees/{employee}   - Update employee
DELETE /admin/employees/{employee}   - Delete employee
GET    /admin/employees/{employee}/edit - Edit form

# Similar patterns for:
/admin/departments
/admin/licenses
/admin/vendors
/admin/user-licenses
/admin/renewals
```

### Authentication Routes
```
GET    /login                        - Login form
POST   /login                        - Authenticate
GET    /register                     - Register form
POST   /register                     - Create account
POST   /logout                       - Logout
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Version History

- **v2.0.0** (2025-12-31) - Employee/User refactoring
  - Separated Employee model from User model
  - Migrated all business logic to Employee model
  - Maintained authentication with User model
  - Added comprehensive documentation

- **v1.0.0** (2025-12-22) - Initial release
  - Core license management functionality
  - User-based system

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For deployment issues or questions:
- Review [DEPLOYMENT.md](DEPLOYMENT.md)
- Check [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md)
- Review Laravel logs in `storage/logs/`
- Contact the development team

---

**Built with Laravel 12** | **Powered by Azure SQL Server** | **Ready for Production**
