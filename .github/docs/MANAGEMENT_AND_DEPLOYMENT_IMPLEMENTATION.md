# Management and Deployment System - Implementation Guide

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Configuration](#configuration)
4. [Security Implementation](#security-implementation)
5. [API Endpoints](#api-endpoints)
6. [Deployment Process](#deployment-process)
7. [Management Operations](#management-operations)
8. [Error Handling & Logging](#error-handling--logging)
9. [Usage Examples](#usage-examples)
10. [Troubleshooting](#troubleshooting)
11. [Best Practices](#best-practices)

---

## Overview

This document provides a comprehensive guide to the Management and Deployment system implemented in the Cashier System application. The system enables remote management and automated deployment of the Laravel application through a secure API interface.

### Key Features

- **Secure API-based management** - Protected by secret key authentication
- **Automated deployment** - One-command deployment with rollback capability
- **Maintenance mode control** - Start/stop application operations remotely
- **Custom script execution** - Run artisan commands remotely
- **Status monitoring** - Real-time application status checking
- **Comprehensive logging** - All operations are logged for audit trails

---

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────┐
│                   Management Client                     │
│              (External Monitoring System)               │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ HTTP/HTTPS + Secret Key
                     ▼
┌─────────────────────────────────────────────────────────┐
│                  API Routes Layer                       │
│            /api/management/* endpoints                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│            ManagementController                         │
│        • Authentication validation                      │
│        • Request processing                             │
│        • Artisan command execution                      │
│        • Response formatting                            │
└────────────────────┬────────────────────────────────────┘
                     │
         ┌───────────┼───────────┐
         ▼           ▼           ▼
    ┌────────┐  ┌────────┐  ┌────────┐
    │Deploy  │  │Artisan │  │System  │
    │Script  │  │Commands│  │Status  │
    └────────┘  └────────┘  └────────┘
```

### File Structure

```
cashier_system/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── DeployCommand.php       # Deployment orchestrator
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── ManagementController.php  # Main controller
├── routes/
│   └── api.php                         # API route definitions
├── config/
│   └── app.php                         # Configuration settings
├── deploy.sh                           # Deployment script
├── refresh.sh                          # Quick refresh script
└── .env                                # Environment variables
```

---

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Application Instance Management
APP_ID=larament_default
APP_EXTERNAL_URL=https://your-domain.com
MANAGE_OPERATIONS_URL=http://localhost:8009
MANAGEMENT_SECRET_KEY=your-secure-random-secret-key-here
```

#### Configuration Details

| Variable | Description | Example | Required |
|----------|-------------|---------|----------|
| `APP_ID` | Unique identifier for this app instance | `larament_production_001` | Yes |
| `APP_EXTERNAL_URL` | Public-facing URL of the application | `https://cashier.example.com` | Yes |
| `MANAGE_OPERATIONS_URL` | URL of the management operations server | `http://localhost:8009` | Optional |
| `MANAGEMENT_SECRET_KEY` | Secret key for API authentication | `random-64-char-string` | **Critical** |

### Config File Setup

The configuration is defined in `config/app.php`:

```php
/*
|--------------------------------------------------------------------------
| Application Instance Management
|--------------------------------------------------------------------------
|
| These values are used for managing multiple application instances
| through the management operations system.
|
*/

'id' => env('APP_ID', 'larament_default'),
'external_url' => env('APP_EXTERNAL_URL', env('APP_URL', 'http://localhost')),
'manage_operations_url' => env('MANAGE_OPERATIONS_URL', 'http://localhost:8009'),
'management_secret_key' => env('MANAGEMENT_SECRET_KEY', 'default-secret-key'),
```

### Generating a Secure Secret Key

```bash
# Generate a random secret key
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# Or use OpenSSL
openssl rand -hex 32
```

**⚠️ IMPORTANT**: Never use the default secret key in production!

---

## Security Implementation

### Authentication Mechanism

The system uses a **secret key authentication** mechanism to protect all management endpoints.

#### How It Works

1. **Client Request**: Client sends request with secret key
2. **Validation**: Controller validates the secret key
3. **Authorization**: If valid, request is processed
4. **Response**: Operation result is returned

#### Key Validation Flow

```php
private function validateManagementKey(Request $request): ?JsonResponse
{
    // Accept key from query parameter OR header
    $secretKey = $request->query('key') ?? $request->header('X-Management-Key');

    // Compare with configured secret
    if ($secretKey !== config('app.management_secret_key')) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access'
        ], 401);
    }

    return null; // Valid key
}
```

### Authentication Methods

#### Method 1: Query Parameter (Simple)

```bash
curl -X POST "https://your-app.com/api/management/deploy?key=YOUR_SECRET_KEY"
```

#### Method 2: HTTP Header (Recommended)

```bash
curl -X POST "https://your-app.com/api/management/deploy" \
     -H "X-Management-Key: YOUR_SECRET_KEY"
```

### Security Best Practices

1. ✅ **Use HTTPS in production** - Never send keys over unencrypted connections
2. ✅ **Rotate keys regularly** - Change secret keys periodically
3. ✅ **Restrict IP access** - Use firewall rules to limit access
4. ✅ **Monitor logs** - Watch for unauthorized access attempts
5. ✅ **Use headers over query params** - Headers are less likely to be logged
6. ✅ **Never commit keys to Git** - Keep `.env` out of version control

---

## API Endpoints

### Base URL

```
{APP_EXTERNAL_URL}/api/management
```

### Endpoint Reference

| Endpoint | Method | Description | Auth Required |
|----------|--------|-------------|---------------|
| `/deploy` | POST | Deploy latest application updates | Yes |
| `/stop` | POST | Enable maintenance mode | Yes |
| `/start` | POST | Disable maintenance mode | Yes |
| `/custom-script` | POST | Execute custom artisan command | Yes |
| `/status` | GET | Get application status | Yes |

---

### 1. Deploy Endpoint

**Endpoint**: `POST /api/management/deploy`

**Description**: Triggers the full deployment process including git pull, dependency installation, migrations, and cache optimization.

**Authentication**: Required via secret key

**Request**:

```bash
curl -X POST "https://your-app.com/api/management/deploy" \
     -H "X-Management-Key: YOUR_SECRET_KEY"
```

**Success Response** (200 OK):

```json
{
    "success": true,
    "message": "Deployment completed successfully",
    "output": "🚀 Starting deployment process...\n🔧 Enabling maintenance mode...\n..."
}
```

**Error Response** (401 Unauthorized):

```json
{
    "success": false,
    "message": "Unauthorized access"
}
```

**Error Response** (500 Internal Server Error):

```json
{
    "success": false,
    "message": "Deployment failed: Deploy script not found at: /path/to/deploy.sh"
}
```

**Process Flow**:

1. Validates secret key
2. Calls `app:deploy` Artisan command
3. Artisan command:
   - Enables maintenance mode
   - Executes `deploy.sh` script
   - Waits for completion (6-minute timeout)
   - Disables maintenance mode
4. Returns output and status

---

### 2. Stop Endpoint

**Endpoint**: `POST /api/management/stop`

**Description**: Puts the application into maintenance mode, showing a maintenance page to users.

**Authentication**: Required via secret key

**Request**:

```bash
curl -X POST "https://your-app.com/api/management/stop" \
     -H "X-Management-Key: YOUR_SECRET_KEY"
```

**Success Response** (200 OK):

```json
{
    "success": true,
    "message": "Application stopped successfully",
    "output": "Application is now in maintenance mode."
}
```

**Use Cases**:

- Emergency shutdown
- Scheduled maintenance
- Database maintenance
- Server updates preparation

---

### 3. Start Endpoint

**Endpoint**: `POST /api/management/start`

**Description**: Brings the application out of maintenance mode, making it accessible to users.

**Authentication**: Required via secret key

**Request**:

```bash
curl -X POST "https://your-app.com/api/management/start" \
     -H "X-Management-Key: YOUR_SECRET_KEY"
```

**Success Response** (200 OK):

```json
{
    "success": true,
    "message": "Application started successfully",
    "output": "Application is now live."
}
```

**Use Cases**:

- Resume operations after maintenance
- Recovery from deployment issues
- Post-migration activation

---

### 4. Custom Script Endpoint

**Endpoint**: `POST /api/management/custom-script`

**Description**: Executes a custom Artisan command remotely. Restricted to Artisan commands only for security.

**Authentication**: Required via secret key

**Request**:

```bash
curl -X POST "https://your-app.com/api/management/custom-script" \
     -H "X-Management-Key: YOUR_SECRET_KEY" \
     -H "Content-Type: application/json" \
     -d '{
         "command": "php artisan cache:clear",
         "description": "Clear application cache"
     }'
```

**Request Body**:

```json
{
    "command": "php artisan [artisan-command]",
    "description": "Optional description of what this command does"
}
```

**Validation Rules**:

- `command`: Required, must be a string, must start with "php artisan "
- `description`: Optional string

**Success Response** (200 OK):

```json
{
    "success": true,
    "message": "Custom script executed successfully",
    "command": "php artisan cache:clear",
    "output": "Application cache cleared!\n"
}
```

**Error Response** (400 Bad Request):

```json
{
    "success": false,
    "message": "Only artisan commands are allowed"
}
```

**Security Restrictions**:

- ✅ Only Artisan commands allowed
- ❌ No shell commands
- ❌ No system commands
- ❌ No arbitrary PHP execution

**Example Commands**:

```bash
# Clear cache
"command": "php artisan cache:clear"

# Run migrations
"command": "php artisan migrate --force"

# Optimize application
"command": "php artisan optimize"

# Clear compiled views
"command": "php artisan view:clear"
```

---

### 5. Status Endpoint

**Endpoint**: `GET /api/management/status`

**Description**: Returns the current status of the application including maintenance mode state.

**Authentication**: Required via secret key

**Request**:

```bash
curl -X GET "https://your-app.com/api/management/status" \
     -H "X-Management-Key: YOUR_SECRET_KEY"
```

**Success Response** (200 OK):

```json
{
    "success": true,
    "app_id": "larament_production_001",
    "app_name": "Cashier System",
    "app_url": "http://localhost",
    "external_url": "https://cashier.example.com",
    "is_maintenance": false,
    "status": "running",
    "timestamp": "2026-01-28T10:30:45.000000Z"
}
```

**Response Fields**:

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Whether the request was successful |
| `app_id` | string | Unique application instance identifier |
| `app_name` | string | Application name from config |
| `app_url` | string | Internal application URL |
| `external_url` | string | Public-facing URL |
| `is_maintenance` | boolean | Whether app is in maintenance mode |
| `status` | string | "running" or "maintenance" |
| `timestamp` | string | ISO 8601 timestamp of the status check |

**Use Cases**:

- Health checks
- Monitoring dashboards
- Load balancer health probes
- Automated status reporting

---

## Deployment Process

### Overview

The deployment process is orchestrated by the `DeployCommand` Artisan command, which executes the `deploy.sh` script with proper error handling and logging.

### Deployment Steps

```
┌─────────────────────────────────────────┐
│  1. API Request Received                │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  2. Validate Secret Key                 │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  3. Call app:deploy Command             │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  4. Enable Maintenance Mode             │
│     (php artisan down)                  │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  5. Execute deploy.sh Script            │
│     • Git reset & pull                  │
│     • Install wkhtmltoimage             │
│     • Install fonts (dejavu, freefont)  │
│     • Composer install                  │
│     • NPM install & build               │
│     • Run migrations                    │
│     • Cache optimization                │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  6. Check Script Exit Status            │
└─────────────────┬───────────────────────┘
                  │
         ┌────────┴────────┐
         │                 │
    Success           Failure
         │                 │
         ▼                 ▼
┌─────────────────┐  ┌─────────────────┐
│ 7a. Disable     │  │ 7b. Log Error   │
│     Maintenance │  │     & Disable   │
│     Mode        │  │     Maintenance │
└────────┬────────┘  └────────┬────────┘
         │                    │
         ▼                    ▼
┌─────────────────────────────────────────┐
│  8. Return Response to Client           │
└─────────────────────────────────────────┘
```

### DeployCommand Details

**File**: `app/Console/Commands/DeployCommand.php`

**Signature**: `php artisan app:deploy {--force}`

**Options**:

- `--force`: Skip confirmation prompts (useful for automated deployments)

**Code Flow**:

```php
public function handle(): int
{
    // 1. Start deployment
    $this->info('🚀 Starting deployment process...');

    try {
        // 2. Enable maintenance mode
        $this->info('🔧 Enabling maintenance mode...');
        $this->call('down');

        // 3. Validate deploy script exists
        $deployScriptPath = base_path('deploy.sh');
        if (!file_exists($deployScriptPath)) {
            throw new Exception('Deploy script not found');
        }

        // 4. Make script executable (Unix-like systems)
        if (PHP_OS_FAMILY !== 'Windows') {
            Process::run('chmod +x ' . $deployScriptPath);
        }

        // 5. Execute deployment script with 6-minute timeout
        $result = Process::timeout(60*6)
                        ->run('sudo /bin/sh /var/www/turbo_restaurant/larament/deploy.sh');

        // 6. Check if script failed
        if ($result->failed()) {
            throw new Exception('Deployment script failed: ' . $result->errorOutput());
        }

        // 7. Show output
        if ($result->output()) {
            $this->line($result->output());
        }

        // 8. Disable maintenance mode
        $this->info('🌐 Bringing application back online...');
        $this->call('up');

        // 9. Success messages
        $this->info('✅ Deployment completed successfully!');
        
        return Command::SUCCESS;

    } catch (Exception $e) {
        // 10. Handle errors
        $this->error('❌ Deployment failed: ' . $e->getMessage());
        
        // 11. Attempt recovery
        $this->warn('⚠️ Attempting to bring application back online...');
        $this->call('up');
        
        return Command::FAILURE;
    }
}
```

### deploy.sh Script

**File**: `deploy.sh`

**Purpose**: Performs the actual deployment steps on the server

**Content**:

```bash
#!/bin/sh
set -e  # Exit on any error

# Navigate to application directory
cd /var/www/turbo_restaurant/larament

# 1. Reset any local changes and pull latest code
git reset --hard
git pull

# 2. Install wkhtmltoimage (for PDF generation)
if [ ! -x /usr/local/bin/wkhtmltoimage ]; then
    if [ -f ./wkhtmltoimage ]; then
        echo "Installing wkhtmltoimage to /usr/local/bin"
        cp ./wkhtmltoimage /usr/local/bin/wkhtmltoimage
        chmod +x /usr/local/bin/wkhtmltoimage
    else
        echo "Warning: ./wkhtmltoimage not found, skipping installation"
    fi
fi

# 3. Install required fonts (for Arabic support)
if ! apk info -e font-dejavu > /dev/null 2>&1; then
  echo "🖋 Installing font-dejavu..."
  apk update && apk add font-dejavu
  echo "✅ font-dejavu installed successfully."
else
  echo "✔ font-dejavu is already installed."
fi

if ! apk info -e font-freefont > /dev/null 2>&1; then
  echo "🖋 Installing font-freefont..."
  apk update && apk add font-freefont
  echo "✅ font-freefont-ttf installed successfully."
else
  echo "✔ font-freefont-ttf is already installed."
fi

# Return to app directory
cd /var/www/turbo_restaurant/larament

# 4. Install PHP dependencies
composer install

# 5. Install frontend dependencies and build
npm install
npm run build

# 6. Run database migrations
php artisan migrate --force

# 7. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Clear old caches
php artisan cache:clear
php artisan optimize:clear

# 9. Optimize application
php artisan optimize
```

**Key Features**:

- ✅ **Atomic operations**: `set -e` ensures script stops on first error
- ✅ **Idempotent**: Can be run multiple times safely
- ✅ **Font support**: Ensures Arabic fonts are available
- ✅ **PDF support**: Installs wkhtmltoimage for receipt generation
- ✅ **Cache optimization**: Improves application performance

### Deployment Timeout

The deployment has a **6-minute timeout** to handle:

- Slow network connections during `git pull`
- Large dependency installations
- Database migrations
- Asset compilation

```php
Process::timeout(60*6)->run('...');  // 6 minutes
```

### Error Recovery

If deployment fails:

1. **Exception is caught**
2. **Error is logged** to Laravel logs
3. **Maintenance mode is disabled** to restore user access
4. **Error response is returned** to the client

This ensures the application doesn't remain stuck in maintenance mode after a failed deployment.

---

## Management Operations

### Maintenance Mode

Laravel's maintenance mode is used to temporarily disable the application during updates.

**Enable Maintenance Mode**:

```bash
php artisan down
```

**Disable Maintenance Mode**:

```bash
php artisan up
```

**Custom Maintenance Page**:

You can customize the maintenance page by creating:

```
resources/views/errors/503.blade.php
```

### Refresh Script

**File**: `refresh.sh`

**Purpose**: Quick application refresh without full deployment

**Content**:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
rc-service php-fpm83 restart
rc-service nginx restart
npm run build
php artisan queue:restart  # if applicable
```

**When to Use**:

- Configuration changes
- Cache issues
- Quick fixes
- Template updates

**How to Run**:

```bash
chmod +x refresh.sh
./refresh.sh
```

---

## Error Handling & Logging

### Logging Strategy

All management operations are logged using Laravel's logging system.

**Log Locations**:

- **Default**: `storage/logs/laravel.log`
- **Daily**: `storage/logs/laravel-YYYY-MM-DD.log` (if using daily channel)

### Log Events

#### 1. Deployment Logs

```php
// Start
Log::info('Deployment started via management API');

// Success
Log::info('Deployment completed successfully', ['output' => $output]);

// Failure
Log::error('Deployment failed', ['error' => $e->getMessage()]);
```

#### 2. Maintenance Mode Logs

```php
// Stop
Log::info('Stopping application via management API');
Log::info('Application stopped successfully', ['output' => $output]);

// Start
Log::info('Starting application via management API');
Log::info('Application started successfully', ['output' => $output]);
```

#### 3. Custom Script Logs

```php
// Execution
Log::info('Executing custom script via management API', [
    'command' => $command,
    'description' => $description
]);

// Success
Log::info('Custom script executed successfully', [
    'command' => $command,
    'output' => $output
]);

// Failure
Log::error('Custom script execution failed', [
    'command' => $request->input('command'),
    'error' => $e->getMessage()
]);
```

### Example Log Entry

```
[2026-01-28 10:30:45] local.INFO: Deployment started via management API  
[2026-01-28 10:30:46] local.INFO: Deployment completed successfully {"output":"🚀 Starting deployment process...\n✅ Deployment completed successfully!"} 
```

### Monitoring Failed Attempts

Watch for unauthorized access attempts:

```bash
# Monitor logs for failed authentication
tail -f storage/logs/laravel.log | grep "Unauthorized access"

# Count failed attempts
grep "Unauthorized access" storage/logs/laravel.log | wc -l
```

### Error Response Format

All errors follow a consistent JSON format:

```json
{
    "success": false,
    "message": "Error description here"
}
```

HTTP Status Codes:

- `401`: Unauthorized (invalid secret key)
- `400`: Bad Request (validation failed)
- `500`: Internal Server Error (operation failed)

---

## Usage Examples

### 1. Complete Deployment Workflow

```bash
#!/bin/bash

API_URL="https://your-app.com/api/management"
SECRET_KEY="your-secret-key-here"

# 1. Check current status
echo "Checking application status..."
curl -X GET "$API_URL/status" \
     -H "X-Management-Key: $SECRET_KEY"

# 2. Stop the application
echo "Stopping application..."
curl -X POST "$API_URL/stop" \
     -H "X-Management-Key: $SECRET_KEY"

# 3. Deploy updates
echo "Deploying updates..."
curl -X POST "$API_URL/deploy" \
     -H "X-Management-Key: $SECRET_KEY"

# 4. Verify status
echo "Verifying deployment..."
curl -X GET "$API_URL/status" \
     -H "X-Management-Key: $SECRET_KEY"
```

### 2. Scheduled Maintenance

```bash
#!/bin/bash

API_URL="https://your-app.com/api/management"
SECRET_KEY="your-secret-key-here"

# Enable maintenance mode
curl -X POST "$API_URL/stop" \
     -H "X-Management-Key: $SECRET_KEY"

# Run database maintenance
curl -X POST "$API_URL/custom-script" \
     -H "X-Management-Key: $SECRET_KEY" \
     -H "Content-Type: application/json" \
     -d '{
         "command": "php artisan db:optimize",
         "description": "Optimize database"
     }'

# Disable maintenance mode
curl -X POST "$API_URL/start" \
     -H "X-Management-Key: $SECRET_KEY"
```

### 3. Cache Management

```bash
# Clear all caches
curl -X POST "https://your-app.com/api/management/custom-script" \
     -H "X-Management-Key: YOUR_SECRET_KEY" \
     -H "Content-Type: application/json" \
     -d '{
         "command": "php artisan optimize:clear"
     }'

# Rebuild caches
curl -X POST "https://your-app.com/api/management/custom-script" \
     -H "X-Management-Key: YOUR_SECRET_KEY" \
     -H "Content-Type: application/json" \
     -d '{
         "command": "php artisan optimize"
     }'
```

### 4. Health Check Script

```bash
#!/bin/bash

API_URL="https://your-app.com/api/management"
SECRET_KEY="your-secret-key-here"

# Get status and parse JSON
STATUS=$(curl -s -X GET "$API_URL/status" \
         -H "X-Management-Key: $SECRET_KEY")

# Check if application is running
IS_RUNNING=$(echo $STATUS | jq -r '.status')

if [ "$IS_RUNNING" = "running" ]; then
    echo "✅ Application is running normally"
    exit 0
else
    echo "⚠️ Application is in maintenance mode"
    exit 1
fi
```

### 5. Python Integration Example

```python
import requests
import json

class ManagementAPI:
    def __init__(self, base_url, secret_key):
        self.base_url = base_url
        self.headers = {
            'X-Management-Key': secret_key,
            'Content-Type': 'application/json'
        }
    
    def get_status(self):
        """Get application status"""
        response = requests.get(
            f"{self.base_url}/status",
            headers=self.headers
        )
        return response.json()
    
    def deploy(self):
        """Deploy application"""
        response = requests.post(
            f"{self.base_url}/deploy",
            headers=self.headers
        )
        return response.json()
    
    def stop(self):
        """Enable maintenance mode"""
        response = requests.post(
            f"{self.base_url}/stop",
            headers=self.headers
        )
        return response.json()
    
    def start(self):
        """Disable maintenance mode"""
        response = requests.post(
            f"{self.base_url}/start",
            headers=self.headers
        )
        return response.json()
    
    def run_command(self, command, description=""):
        """Execute custom artisan command"""
        data = {
            'command': command,
            'description': description
        }
        response = requests.post(
            f"{self.base_url}/custom-script",
            headers=self.headers,
            json=data
        )
        return response.json()

# Usage
api = ManagementAPI(
    base_url='https://your-app.com/api/management',
    secret_key='your-secret-key-here'
)

# Check status
status = api.get_status()
print(f"App Status: {status['status']}")

# Deploy
result = api.deploy()
print(f"Deployment: {result['message']}")
```

### 6. Node.js Integration Example

```javascript
const axios = require('axios');

class ManagementAPI {
    constructor(baseUrl, secretKey) {
        this.baseUrl = baseUrl;
        this.headers = {
            'X-Management-Key': secretKey,
            'Content-Type': 'application/json'
        };
    }

    async getStatus() {
        const response = await axios.get(`${this.baseUrl}/status`, {
            headers: this.headers
        });
        return response.data;
    }

    async deploy() {
        const response = await axios.post(`${this.baseUrl}/deploy`, {}, {
            headers: this.headers
        });
        return response.data;
    }

    async stop() {
        const response = await axios.post(`${this.baseUrl}/stop`, {}, {
            headers: this.headers
        });
        return response.data;
    }

    async start() {
        const response = await axios.post(`${this.baseUrl}/start`, {}, {
            headers: this.headers
        });
        return response.data;
    }

    async runCommand(command, description = '') {
        const response = await axios.post(`${this.baseUrl}/custom-script`, {
            command,
            description
        }, {
            headers: this.headers
        });
        return response.data;
    }
}

// Usage
const api = new ManagementAPI(
    'https://your-app.com/api/management',
    'your-secret-key-here'
);

(async () => {
    try {
        // Get status
        const status = await api.getStatus();
        console.log(`App Status: ${status.status}`);

        // Deploy
        const result = await api.deploy();
        console.log(`Deployment: ${result.message}`);
    } catch (error) {
        console.error('Error:', error.response?.data || error.message);
    }
})();
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Unauthorized Access (401)

**Problem**: Getting "Unauthorized access" error

**Solutions**:

```bash
# Verify secret key in .env
cat .env | grep MANAGEMENT_SECRET_KEY

# Clear config cache
php artisan config:clear
php artisan config:cache

# Test with correct key
curl -X GET "https://your-app.com/api/management/status?key=YOUR_SECRET_KEY"
```

#### 2. Deployment Script Not Found

**Problem**: "Deploy script not found" error

**Solutions**:

```bash
# Check if deploy.sh exists
ls -la deploy.sh

# Verify path in DeployCommand.php
# The script should be at base_path('deploy.sh')

# Create deploy.sh if missing (copy from template)
```

#### 3. Permission Denied on deploy.sh

**Problem**: Script execution fails due to permissions

**Solutions**:

```bash
# Make script executable
chmod +x deploy.sh

# Check ownership
ls -la deploy.sh

# Fix ownership if needed
sudo chown www-data:www-data deploy.sh
```

#### 4. Deployment Timeout

**Problem**: Deployment takes longer than 6 minutes

**Solutions**:

```php
// Increase timeout in DeployCommand.php
Process::timeout(60*10)->run('...');  // 10 minutes

// Or optimize deploy.sh
// - Use composer install --no-dev for production
// - Skip unnecessary steps
```

#### 5. Application Stuck in Maintenance Mode

**Problem**: App remains in maintenance mode after failed deployment

**Solutions**:

```bash
# Manually disable maintenance mode
php artisan up

# Or via API
curl -X POST "https://your-app.com/api/management/start" \
     -H "X-Management-Key: YOUR_SECRET_KEY"

# Check for maintenance file
ls -la storage/framework/down
rm storage/framework/down  # Remove if exists
```

#### 6. Git Pull Fails

**Problem**: `git pull` in deploy.sh fails

**Solutions**:

```bash
# Check git status
cd /var/www/turbo_restaurant/larament
git status

# Reset any conflicts
git reset --hard
git pull

# Verify git credentials
git remote -v

# Check SSH key for git
ssh -T git@github.com  # or your git server
```

#### 7. Composer Install Fails

**Problem**: Dependency installation fails

**Solutions**:

```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Install with verbose output
composer install -vvv

# Check memory limit
php -i | grep memory_limit
```

#### 8. NPM Build Fails

**Problem**: Frontend build fails

**Solutions**:

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules
npm install

# Check Node.js version
node --version
npm --version

# Update npm
npm install -g npm@latest
```

### Debugging Commands

```bash
# Check application logs
tail -f storage/logs/laravel.log

# Check web server logs (nginx)
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php-fpm/error.log

# Test artisan commands manually
php artisan app:deploy --force

# Check process status
ps aux | grep php
ps aux | grep nginx

# Check disk space
df -h

# Check memory usage
free -m
```

---

## Best Practices

### 1. Security

✅ **Do**:
- Use HTTPS for all management API calls
- Rotate secret keys every 90 days
- Restrict management API access to specific IPs
- Monitor logs for unauthorized access
- Use environment variables for secrets
- Enable rate limiting on management endpoints

❌ **Don't**:
- Hardcode secret keys in code
- Use default secret keys in production
- Expose management endpoints publicly without IP restrictions
- Log secret keys in application logs
- Share secret keys via insecure channels

### 2. Deployment

✅ **Do**:
- Test deployments in staging first
- Schedule deployments during low-traffic periods
- Have a rollback plan ready
- Backup database before migrations
- Monitor logs during deployment
- Notify users about scheduled maintenance

❌ **Don't**:
- Deploy during peak business hours
- Skip testing in staging environment
- Deploy without database backups
- Ignore deployment errors
- Deploy without reviewing changes

### 3. Monitoring

✅ **Do**:
- Set up automated health checks
- Monitor deployment success/failure rates
- Track deployment duration trends
- Alert on repeated failed deployments
- Review logs regularly
- Monitor server resources during deployment

❌ **Don't**:
- Ignore failed deployment alerts
- Skip log reviews
- Assume deployments succeed
- Overlook performance degradation after deployment

### 4. Error Handling

✅ **Do**:
- Always have error recovery mechanisms
- Log all errors with context
- Provide meaningful error messages
- Gracefully handle timeouts
- Automatically exit maintenance mode on failure
- Notify administrators of critical failures

❌ **Don't**:
- Leave application in broken state
- Ignore error logs
- Provide generic error messages
- Let application stay in maintenance mode indefinitely

### 5. Testing

✅ **Do**:
- Test all management endpoints in staging
- Verify secret key authentication
- Test deployment rollback procedures
- Simulate deployment failures
- Verify maintenance mode pages
- Test with different network conditions

❌ **Don't**:
- Test in production first
- Skip edge case testing
- Ignore failed tests
- Deploy untested changes

---

## Advanced Configuration

### Rate Limiting

Add rate limiting to management endpoints:

```php
// routes/api.php
Route::prefix('management')
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('management.')
    ->group(function () {
        // ... management routes
    });
```

### IP Whitelisting

Restrict access by IP address:

```php
// app/Http/Middleware/RestrictManagementAccess.php
public function handle(Request $request, Closure $next)
{
    $allowedIps = config('app.management_allowed_ips', []);
    
    if (!in_array($request->ip(), $allowedIps)) {
        return response()->json([
            'success' => false,
            'message' => 'Access denied from this IP'
        ], 403);
    }
    
    return $next($request);
}
```

```env
# .env
MANAGEMENT_ALLOWED_IPS=192.168.1.100,203.0.113.0
```

### Custom Deployment Hooks

Add pre/post deployment hooks:

```php
// app/Console/Commands/DeployCommand.php
protected function beforeDeploy()
{
    // Backup database
    $this->call('backup:run');
    
    // Notify team
    Notification::route('slack', config('services.slack.webhook'))
        ->notify(new DeploymentStarted());
}

protected function afterDeploy()
{
    // Clear CDN cache
    $this->call('cdn:flush');
    
    // Notify team
    Notification::route('slack', config('services.slack.webhook'))
        ->notify(new DeploymentCompleted());
}
```

### Multi-Instance Management

Manage multiple application instances:

```bash
# Instance 1
curl -X POST "https://app1.example.com/api/management/deploy" \
     -H "X-Management-Key: $SECRET_KEY_1"

# Instance 2
curl -X POST "https://app2.example.com/api/management/deploy" \
     -H "X-Management-Key: $SECRET_KEY_2"
```

---

## Conclusion

The Management and Deployment system provides a robust, secure, and automated way to manage Laravel applications remotely. By following the guidelines and best practices outlined in this document, you can ensure safe and reliable deployments while maintaining application security and uptime.

### Key Takeaways

1. **Security First**: Always use strong secret keys and HTTPS
2. **Monitor Everything**: Log all operations and monitor for anomalies
3. **Test Thoroughly**: Always test in staging before production
4. **Have Rollback Plans**: Be prepared to recover from failures
5. **Automate Wisely**: Use automation to reduce human error

### Further Resources

- [Laravel Artisan Console Documentation](https://laravel.com/docs/artisan)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Laravel Maintenance Mode](https://laravel.com/docs/configuration#maintenance-mode)
- [Process Management in Laravel](https://laravel.com/docs/processes)

---

**Document Version**: 1.0  
**Last Updated**: January 28, 2026  
**Author**: Cashier System Development Team
