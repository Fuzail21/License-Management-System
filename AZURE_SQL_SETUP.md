# Azure SQL Database Setup Guide

## Step 1: Create a Database User in Azure SQL Database

You have two options for creating users in Azure SQL Database:

### Option A: Using Azure Portal (SQL Authentication)

1. **Log into Azure Portal** (https://portal.azure.com)
2. Navigate to your SQL Server: `2020sqlwebapp`
3. Go to **SQL databases** → Select your database: `license-management`
4. Click on **Query editor (preview)** in the left menu
5. Login with your server admin credentials:
   - Login: `wms-laravel-server-admin`
   - Password: `BGQ14S2W1KY2I30I$`

6. **Run this SQL to create a new user:**
   ```sql
   -- Create a login (server-level)
   CREATE LOGIN [app_user] WITH PASSWORD = 'YourStrongPassword123!';
   
   -- Create a user in your database
   USE [license-management];
   CREATE USER [app_user] FROM LOGIN [app_user];
   
   -- Grant necessary permissions
   ALTER ROLE db_owner ADD MEMBER [app_user];
   -- OR for more restricted access:
   -- ALTER ROLE db_datareader ADD MEMBER [app_user];
   -- ALTER ROLE db_datawriter ADD MEMBER [app_user];
   -- ALTER ROLE db_ddladmin ADD MEMBER [app_user];
   ```

### Option B: Using Azure Data Studio or SQL Server Management Studio (SSMS)

1. **Connect to your Azure SQL Server:**
   - Server: `2020sqlwebapp.database.windows.net`
   - Authentication: SQL Server Authentication
   - Login: `wms-laravel-server-admin`
   - Password: `BGQ14S2W1KY2I30I$`
   - Database: `license-management`

2. **Run the same SQL commands as above**

### Option C: Using Azure CLI

```bash
# Install Azure CLI if not installed
# Then login
az login

# Create a SQL user
az sql server ad-admin create \
  --resource-group <your-resource-group> \
  --server-name 2020sqlwebapp \
  --display-name <admin-name> \
  --object-id <object-id>
```

## Step 2: Configure Firewall Rules

Azure SQL Database requires firewall rules to allow connections:

1. **In Azure Portal:**
   - Go to your SQL Server: `2020sqlwebapp`
   - Click on **Networking** or **Firewalls and virtual networks**
   - Click **Add client IP** to allow your current IP
   - Or add a range: `0.0.0.0 - 255.255.255.255` (for development only - NOT recommended for production)

2. **For Production:**
   - Only allow specific IP addresses
   - Consider using Azure Virtual Network rules
   - Enable **Allow Azure services and resources to access this server** if deploying to Azure

## Step 3: Update Laravel Configuration

### Update `.env` file:

```env
DB_CONNECTION=sqlsrv
DB_HOST=2020sqlwebapp.database.windows.net
DB_PORT=1433
DB_DATABASE=license-management
DB_USERNAME=app_user
DB_PASSWORD=YourStrongPassword123!
DB_CHARSET=utf8
```

### Update `config/database.php`:

The configuration should use environment variables (already set up in the commented section).

## Step 4: Install SQL Server Driver for PHP

Laravel requires the SQL Server PDO driver:

### On Windows:
```bash
# Install via PECL (if available)
pecl install sqlsrv

# Or download from Microsoft:
# https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
```

### On Linux:
```bash
# Install Microsoft ODBC Driver
curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql17
sudo apt-get install -y unixodbc-dev

# Install PHP SQL Server extension
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv

# Add to php.ini
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.2/fpm/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.2/fpm/php.ini
```

### Verify Installation:
```bash
php -m | grep sqlsrv
php -m | grep pdo_sqlsrv
```

## Step 5: Test Connection

```bash
php artisan tinker
```

Then in tinker:
```php
DB::connection('sqlsrv')->getPdo();
// Should return PDO object without errors
```

## Step 6: Run Migrations

```bash
php artisan migrate
```

## Security Best Practices

1. ✅ **Never hardcode credentials** in config files
2. ✅ Use **strong passwords** (minimum 12 characters, mixed case, numbers, symbols)
3. ✅ **Limit user permissions** - don't use `db_owner` unless necessary
4. ✅ **Restrict firewall rules** - only allow necessary IPs
5. ✅ **Use Azure Key Vault** for storing secrets in production
6. ✅ **Enable SSL/TLS** encryption (Azure SQL does this by default)
7. ✅ **Regularly rotate passwords**

## Recommended User Permissions

For a Laravel application, you typically need:
- `db_datareader` - Read data
- `db_datawriter` - Write data
- `db_ddladmin` - Create/modify tables (for migrations)

```sql
-- Create user with limited permissions
CREATE LOGIN [app_user] WITH PASSWORD = 'YourStrongPassword123!';
USE [license-management];
CREATE USER [app_user] FROM LOGIN [app_user];

-- Grant specific roles
ALTER ROLE db_datareader ADD MEMBER [app_user];
ALTER ROLE db_datawriter ADD MEMBER [app_user];
ALTER ROLE db_ddladmin ADD MEMBER [app_user];
```

## Troubleshooting

### Connection Timeout
- Check firewall rules in Azure Portal
- Verify server name and port (1433)
- Check if your IP is allowed

### Authentication Failed
- Verify username and password
- Check if user exists in the database
- Ensure login exists at server level

### Driver Not Found
- Install SQL Server PHP drivers
- Check `php.ini` has extensions enabled
- Restart web server after installing drivers

### SSL/TLS Issues
- Azure SQL requires encrypted connections
- Add `'encrypt' => true` to database config
- Or use `'trust_server_certificate' => true` for development

## Additional Resources

- [Azure SQL Database Documentation](https://docs.microsoft.com/azure/azure-sql/)
- [Laravel SQL Server Configuration](https://laravel.com/docs/database#sql-server-configuration)
- [PHP SQL Server Driver](https://docs.microsoft.com/sql/connect/php/)

