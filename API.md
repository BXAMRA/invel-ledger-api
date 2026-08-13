# Invel Ledger API Documentation

**Base URL:** `https://ledger.bxamra.dev/api`

## Global Response Format

All successful endpoints return a normalized JSON structure:
```json
{
  "success": true,
  "message": "Descriptive message",
  "data": { ... } // or null
}
```
Error responses return a `4xx` or `5xx` status code with the following structure:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // Validation errors if applicable, or null
}
```

---

## Authentication

All protected routes require a Bearer token in the `Authorization` header:
`Authorization: Bearer <token>`

### POST `/login`
**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "yourpassword"
}
```
**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abcdef123456",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

### POST `/logout` (Protected)
**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

### GET `/user` (Protected)
**Response:** `200 OK`
Returns the currently authenticated user object.

---

## Setup & Onboarding (Unprotected)

### POST `/setup/fresh`
Initializes the application for the first time.
**Request Body (`application/json`):**
```json
{
  "admin": {
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "securepassword"
  },
  "business": {
    "name": "BXAMRA IT Solutions",
    "email": "admin@bxamra.dev"
  },
  "banking": {
    "bankName": "State Bank Of India",
    "accountNumber": "123456789",
    "ifsc": "SBIN000123"
  }
}
```
**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Fresh setup completed successfully",
  "data": null
}
```

### POST `/setup/restore`
Restores the application from a legacy backup JSON file and creates an admin account.
**Request (`multipart/form-data`):**
- `file`: The legacy JSON backup file
- `admin_email`: Email for the new admin account
- `admin_password`: Password for the new admin account

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data restored successfully from legacy format",
  "data": null
}
```

---

## Backup (Protected)

### GET `/backup`
Generates and downloads a complete backup of the database in the new standard JSON format.
**Response:** `200 OK`
- Returns a raw file download: `invel-ledger-backup-YYYY-MM-DD.json`
- `Content-Type: application/json`

### POST `/backup/import`
Restores the database from a new standard JSON backup file. **Warning: This completely wipes the current database.**
**Request (`multipart/form-data`):**
- `file`: The standard JSON backup file

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Backup restored successfully",
  "data": null
}
```

---

## Core Resources (Protected)

The API provides standard RESTful endpoints for the following resources:
- `/customers`
- `/services`
- `/bundles`
- `/documents` (Invoices, Quotes, Estimates)
- `/payments`

### Standard Resource Endpoints (e.g., `/customers`):
- `GET /customers` - List all records
- `GET /customers/{id}` - Get a single record
- `POST /customers` - Create a new record
- `PUT /customers/{id}` - Update a record
- `DELETE /customers/{id}` - Delete a record

*Note: The specific request body validations and processing logic (such as calculating document subtotals) for the Create/Update routes of these resources are pending implementation in their respective controllers.*
