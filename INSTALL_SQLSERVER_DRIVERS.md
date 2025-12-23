# Installing SQL Server Drivers for PHP on Windows

## Quick Installation Guide

You need to install the Microsoft SQL Server drivers for PHP to connect to Azure SQL Database.

### Step 1: Download SQL Server Drivers

1. **Download the Microsoft ODBC Driver for SQL Server:**
   - Go to: https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
   - Download: **ODBC Driver 18 for SQL Server** (or ODBC Driver 17)
   - Install the downloaded `.msi` file

2. **Download PHP SQL Server Extensions:**
   - Go to: https://github.com/Microsoft/msphpsql/releases
   - Download the latest release for **PHP 8.2 x64 (Thread Safe)**
   - Look for files: `php_pdo_sqlsrv_82_ts_x64.dll` and `php_sqlsrv_82_ts_x64.dll`

### Step 2: Install the Extensions

1. **Find your PHP extension directory:**
   ```powershell
   php --ini
   ```
   Look for "Scan this dir for additional .ini files" - this is usually `C:\php\ext\` or similar.

2. **Copy the DLL files:**
   - Copy `php_pdo_sqlsrv_82_ts_x64.dll` to your PHP `ext` directory
   - Copy `php_sqlsrv_82_ts_x64.dll` to your PHP `ext` directory

3. **Enable the extensions in php.ini:**
   - Find your `php.ini` file (run `php --ini` to locate it)
   - Add these lines:
   ```ini
   extension=sqlsrv
   extension=pdo_sqlsrv
   ```

4. **Restart your web server** (if using Apache/IIS) or just restart PHP-FPM

### Step 3: Verify Installation

```powershell
php -m | findstr sqlsrv
```

You should see:
```
pdo_sqlsrv
sqlsrv
```

### Alternative: Using XAMPP/WAMP

If you're using XAMPP or WAMP:

1. **XAMPP:**
   - Download drivers from: https://github.com/Microsoft/msphpsql/releases
   - Copy DLLs to: `C:\xampp\php\ext\`
   - Edit: `C:\xampp\php\php.ini`
   - Add: `extension=sqlsrv` and `extension=pdo_sqlsrv`
   - Restart Apache

2. **WAMP:**
   - Same process, but paths are: `C:\wamp64\bin\php\php8.2.x\ext\`

### Step 4: Test Connection

After installing, try running migrations again:
```bash
php artisan migrate
```

## Troubleshooting

### "could not find driver" error
- Make sure DLL files are in the correct `ext` directory
- Verify `php.ini` has the extension lines uncommented
- Check that you downloaded the **Thread Safe (TS)** version for your PHP build
- Restart your web server/PHP

### "Unable to load dynamic library"
- Check PHP version matches (8.2)
- Check architecture matches (x64)
- Verify Thread Safe vs Non-Thread Safe matches your PHP build
- Check file paths in php.ini are correct

### Connection timeout
- Check Azure SQL firewall rules
- Verify server name and credentials in `.env`

## Quick Download Links

- **ODBC Driver 18:** https://go.microsoft.com/fwlink/?linkid=2249004
- **PHP Drivers (GitHub):** https://github.com/Microsoft/msphpsql/releases
- **Documentation:** https://learn.microsoft.com/en-us/sql/connect/php/

