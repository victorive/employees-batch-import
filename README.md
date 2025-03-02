# Employees Batch Import

## Setup Instructions

### Without Docker:

- **PHP 8.2+**
- **Composer**

**Step 1:** Clone the repository in your terminal using `https://github.com/victorive/employees-batch-import.git`

**Step 2:** Navigate to the project’s directory using `cd employees-batch-import`

**Step 3:** Run `composer install` to install the project’s dependencies.

**Step 4:** Run `cp .env.example .env` to create the .env file for the project’s configuration.

**Step 5:** Run `php artisan key:generate` to set the application key.

**Step 6:** Create a database with the name **employees_batch_import** or any name of your choice in your current database
server and configure the DB_DATABASE, DB_USERNAME and DB_PASSWORD credentials respectively, in the .env files located in
the project’s root folder. eg.

> DB_DATABASE={{your database name}}
>
> DB_USERNAME= {{your database username}}
>
> DB_PASSWORD= {{your database password}}
>
>

**Step 7:** Run `php artisan migrate` to create your database tables.

**Step 8:** Run `php artisan queue:work` to run the jobs for caching the images contained in each item in the feed. To assign 
multiple workers to the queue and process jobs concurrently, you can start multiple `queue:work` processes by opening up 
multiple tabs in your terminal and running the command.

**Step 9:** Run `php artisan serve` to serve your application, then use the link generated to access the app via any
API testing tool of your choice.

## API Endpoints

- **POST** `/api/employee` - Import CSV
- **GET** `/api/employee/{id}` - Get employee
- **DELETE** `/api/employee/{id}` - Delete employee

## Import CSV

To import a CSV file, use the following command:
```bash
curl -X POST -F "file=@import.csv" http://localhost:8000/api/employee
```

## What I Would Have Done Differently/Additionally

If this were a real-world production scenario, I would have implemented the following enhancements to make the system more robust, secure, and user-friendly:

### 1. **Authentication & Role-Based Access Control (RBAC)**
   - Add an **authentication layer** (e.g., JWT or OAuth2) to secure the API.
   - Implement **RBAC** to ensure only authorized users with specific roles (e.g., `admin`, `manager`) can execute sensitive operations like importing or deleting employees.

### 2. **Job Status Notifications**
   - Send **real-time notifications** (via WebSocket, Email, SMS, or Push) to users when:
     - An import job starts.
     - A job completes successfully.
     - A job fails or encounters errors.
   - This ensures users are informed about the status of long-running processes without manually polling the system.

### 3. **Uniform Response Utility**
   - Create a **response wrapper utility** to standardize API responses. For example:
     ```json
     {
       "status": true,
       "message": "Some message",
       "data": { ... },
       "meta": { ... } // Pagination or additional metadata
     }
     ```
   - This ensures consistency across all endpoints and simplifies client-side handling.

### 4. **Enhanced Test Coverage**
   - Add more **edge-case test scenarios**, such as:
     - Importing files with missing or malformed headers.
     - Handling extremely large files (>1GB).
     - Testing with different delimiters and file encodings.
     - Simulating database failures or network interruptions during imports.

### 5. **Job Progress Tracking**
   - Add an endpoint to **poll the status of a job** (e.g., `GET /api/jobs/{id}`) with details like:
     - Total rows processed.
     - Rows succeeded/failed.
     - Estimated time remaining.
   - This allows users to monitor the progress of long-running jobs in real-time.

### 6. Add **rate limiting** to prevent abuse of the import endpoint
---
