# Van Module APIs - Implementation Summary

## Overview
Successfully created authentication and profile APIs for the Van module following v2 structure with JWT token authentication.

## Changes Made

### 1. **Van Model Updates** (`app/Models/Van.php`)
- Extended `Authenticatable` instead of `Model`
- Implemented `JWTSubject` interface
- Added `Notifiable` trait
- Added `password` to `$hidden` array
- Implemented JWT methods:
  - `getJWTIdentifier()` - Returns the unique identifier for JWT
  - `getJWTCustomClaims()` - Returns custom JWT claims

### 2. **Authentication Configuration** (`config/auth.php`)
- Added `van` guard with JWT driver
- Added `vans` provider pointing to `App\Models\Van` model

### 3. **Van Actions** (Created in `app/Actions/Van/`)
- **LoginVanAction.php**: Handles van driver login
  - Validates credentials
  - Generates JWT token
  - Returns van data with token information
  
- **GetVanByIdAction.php**: Fetches van data by ID
  - Used for the sync endpoint
  - Supports column selection

### 4. **Van Request Validation** (`app/Http/Requests/Van/LoginVanRequest.php`)
- Validates `user_name` (required, max 20 chars)
- Validates `password` (required, min 6 chars)

### 5. **Van Controller** (`app/Http/Controllers/Api/v2/VanController.php`)
- **loginVan() function**: 
  - POST endpoint for van driver authentication
  - Returns: `access_token`, `token_type`, `expires_in`, van details (operative, van_id, number_plate, user_name)
  
- **listById() function**:
  - GET endpoint for data synchronization
  - Protected with JWT verification for van guard
  - Returns complete van information

### 6. **Routes Configuration** (`routes/api.php`)
- Updated van routes:
  - `POST /api/van/login` - Public endpoint (no JWT required)
  - `GET /api/van/sync` - Protected endpoint (JWT verification required)

### 7. **JWT Middleware Enhancement** (`app/Http/Middleware/JwtMiddleware.php`)
- Updated to support guard parameters
- Now handles specific guards like `jwt.verify:van`

## API Endpoints

### 1. POST `/van/login`
**Description**: Authenticate van driver with JWT token

**Request Body**:
```json
{
  "user_name": "van_driver_username",
  "password": "password123"
}
```

**Success Response (200)**:
```json
{
  "data": {
    "id": 1,
    "operative": "John Doe",
    "van_id": 1,
    "number_plate": "ABC123",
    "user_name": "van_driver",
    "access_token": "eyJ1...",
    "token_type": "bearer",
    "expires_in": 3600
  },
  "status": true,
  "message": "Van driver logged in successfully"
}
```

**Error Response (401)**:
```json
{
  "data": [],
  "status": false,
  "message": "Invalid credentials"
}
```

### 2. GET `/van/sync`
**Description**: Retrieve van information for data synchronization (manually triggered)

**Headers**:
```
Authorization: Bearer {access_token}
```

**Success Response (200)**:
```json
{
  "data": {
    "id": 1,
    "company_id": 1,
    "user_name": "van_driver",
    "operative": "John Doe",
    "number_plate": "ABC123",
    "payload": 500,
    "width": 2.5,
    "height": 2.0,
    "length": 5.0,
    "created_at": "2026-03-07T21:51:34.000000Z"
  },
  "status": true,
  "message": ""
}
```

**Error Response (401)**:
```json
{
  "data": [],
  "status": false,
  "message": "Unauthorized"
}
```

## Testing

To test these endpoints:

1. **Login**:
```bash
curl -X POST http://localhost:8000/api/van/login \
  -H "Content-Type: application/json" \
  -d '{"user_name":"van_username","password":"password123"}'
```

2. **Sync** (using token from login):
```bash
curl -X GET http://localhost:8000/api/van/sync \
  -H "Authorization: Bearer {token_from_login}"
```

## Security Features
- JWT token-based authentication
- Password hashing using bcrypt
- Guard-specific authentication with separate provider
- Token expiration (TTL configurable in JWT config)
- Protected sync endpoint (requires valid JWT token)
