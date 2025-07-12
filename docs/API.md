# 📊 API Documentation

## Overview

The Zoo Management System provides a RESTful API for managing users, tickets, and administrative functions. All API endpoints return JSON responses and use standard HTTP status codes.

## Base URL

```
http://localhost/Tugas13/api/
```

## Authentication

### Session-Based Authentication

Most endpoints require user authentication via PHP sessions:

```php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
```

### Admin Authentication

Admin endpoints require admin role verification:

```php
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
```

## Response Format

### Success Response

```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation successful"
}
```

### Error Response

```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable error message"
    }
}
```

## Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 500 | Internal Server Error - Server error |

## Endpoints

### Authentication

#### POST /api/auth/login

Authenticate user and create session.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "username": "johndoe",
        "email": "user@example.com",
        "role": "user"
    },
    "message": "Login successful"
}
```

#### POST /api/auth/register

Register new user account.

**Request:**
```json
{
    "username": "johndoe",
    "email": "user@example.com",
    "password": "password123",
    "confirm_password": "password123",
    "full_name": "John Doe",
    "phone": "+6281234567890"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "username": "johndoe",
        "email": "user@example.com"
    },
    "message": "Registration successful"
}
```

#### POST /api/auth/logout

Terminate user session.

**Response:**
```json
{
    "success": true,
    "message": "Logout successful"
}
```

### User Management

#### GET /api/users/profile

Get current user profile information.

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "username": "johndoe",
        "email": "user@example.com",
        "full_name": "John Doe",
        "phone": "+6281234567890",
        "created_at": "2024-01-15 10:30:00",
        "last_login": "2024-01-20 09:15:00"
    }
}
```

#### PUT /api/users/profile

Update user profile information.

**Request:**
```json
{
    "full_name": "John Doe Updated",
    "phone": "+6281234567891",
    "current_password": "oldpassword",
    "new_password": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully"
}
```

#### GET /api/users/bookings

Get user's booking history.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10)
- `status` (optional): Filter by booking status

**Response:**
```json
{
    "success": true,
    "data": {
        "bookings": [
            {
                "booking_id": 1,
                "ticket_type": "Adult",
                "quantity": 2,
                "total_price": 100000,
                "booking_date": "2024-01-15",
                "visit_date": "2024-01-20",
                "status": "confirmed",
                "payment_status": "paid"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 50,
            "items_per_page": 10
        }
    }
}
```

### Ticket Management

#### GET /api/tickets

Get available ticket types and pricing.

**Response:**
```json
{
    "success": true,
    "data": {
        "ticket_types": [
            {
                "id": 1,
                "type": "Adult",
                "price": 50000,
                "description": "Adult ticket for ages 13 and above",
                "available": true
            },
            {
                "id": 2,
                "type": "Child",
                "price": 25000,
                "description": "Child ticket for ages 3-12",
                "available": true
            }
        ]
    }
}
```

#### POST /api/tickets

Create new ticket booking.

**Request:**
```json
{
    "ticket_type_id": 1,
    "quantity": 2,
    "visit_date": "2024-01-25",
    "visitor_info": {
        "primary_contact": "John Doe",
        "phone": "+6281234567890",
        "email": "john@example.com"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "booking_id": 123,
        "booking_reference": "ZOO2024012501",
        "total_price": 100000,
        "payment_deadline": "2024-01-22 23:59:59",
        "payment_url": "/pages/tiket_bayar.php?booking=123"
    },
    "message": "Booking created successfully"
}
```

#### GET /api/tickets/{id}

Get specific ticket booking details.

**Response:**
```json
{
    "success": true,
    "data": {
        "booking_id": 123,
        "booking_reference": "ZOO2024012501",
        "ticket_type": "Adult",
        "quantity": 2,
        "total_price": 100000,
        "booking_date": "2024-01-15",
        "visit_date": "2024-01-25",
        "status": "confirmed",
        "payment_status": "paid",
        "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
        "visitor_info": {
            "primary_contact": "John Doe",
            "phone": "+6281234567890",
            "email": "john@example.com"
        }
    }
}
```

#### PUT /api/tickets/{id}

Update ticket booking (limited fields).

**Request:**
```json
{
    "visit_date": "2024-01-26",
    "visitor_info": {
        "phone": "+6281234567891"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Booking updated successfully"
}
```

### Admin Endpoints

#### GET /api/admin/stats

Get system statistics (Admin only).

**Response:**
```json
{
    "success": true,
    "data": {
        "total_users": 1250,
        "total_bookings": 3450,
        "revenue_today": 2500000,
        "revenue_month": 75000000,
        "active_sessions": 45,
        "popular_dates": [
            "2024-01-20",
            "2024-01-21",
            "2024-01-27"
        ]
    }
}
```

#### GET /api/admin/users

Get all users with pagination (Admin only).

**Query Parameters:**
- `page` (optional): Page number
- `limit` (optional): Items per page
- `search` (optional): Search term
- `role` (optional): Filter by role

**Response:**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "user_id": 1,
                "username": "johndoe",
                "email": "john@example.com",
                "full_name": "John Doe",
                "role": "user",
                "status": "active",
                "created_at": "2024-01-15 10:30:00",
                "last_login": "2024-01-20 09:15:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 25,
            "total_items": 1250,
            "items_per_page": 50
        }
    }
}
```

#### GET /api/admin/reports

Generate various reports (Admin only).

**Query Parameters:**
- `type`: Report type (revenue, visitors, bookings)
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)
- `format` (optional): Response format (json, csv)

**Response:**
```json
{
    "success": true,
    "data": {
        "report_type": "revenue",
        "period": "2024-01-01 to 2024-01-31",
        "summary": {
            "total_revenue": 75000000,
            "total_bookings": 1500,
            "average_booking_value": 50000
        },
        "details": [
            {
                "date": "2024-01-01",
                "revenue": 2500000,
                "bookings": 50,
                "visitors": 100
            }
        ]
    }
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `AUTH_REQUIRED` | Authentication required |
| `INVALID_CREDENTIALS` | Invalid email or password |
| `ACCESS_DENIED` | Insufficient permissions |
| `USER_NOT_FOUND` | User account not found |
| `BOOKING_NOT_FOUND` | Booking not found |
| `INVALID_DATE` | Invalid date format or past date |
| `INSUFFICIENT_CAPACITY` | Not enough tickets available |
| `PAYMENT_REQUIRED` | Payment required before access |
| `VALIDATION_ERROR` | Request validation failed |

## Rate Limiting

- **Public endpoints**: 100 requests per minute per IP
- **Authenticated endpoints**: 500 requests per minute per user
- **Admin endpoints**: 1000 requests per minute per admin

## CORS Support

The API supports Cross-Origin Resource Sharing (CORS) for web applications:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

## SDK Examples

### JavaScript/jQuery

```javascript
// Login example
$.ajax({
    url: '/api/auth/login',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    }),
    success: function(response) {
        console.log('Login successful:', response);
    },
    error: function(xhr) {
        console.log('Login failed:', xhr.responseJSON);
    }
});

// Book ticket example
$.ajax({
    url: '/api/tickets',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
        ticket_type_id: 1,
        quantity: 2,
        visit_date: '2024-01-25',
        visitor_info: {
            primary_contact: 'John Doe',
            phone: '+6281234567890',
            email: 'john@example.com'
        }
    }),
    success: function(response) {
        window.location.href = response.data.payment_url;
    }
});
```

### PHP

```php
// Login example
$data = json_encode([
    'email' => 'user@example.com',
    'password' => 'password123'
]);

$ch = curl_init('/api/auth/login');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    echo "Login successful";
} else {
    echo "Login failed: " . $result['error']['message'];
}
```

## Webhooks

### Payment Notifications

The system can receive payment notifications via webhooks:

**Endpoint:** `POST /api/webhooks/payment`

**Payload:**
```json
{
    "booking_id": 123,
    "payment_status": "paid",
    "transaction_id": "TXN123456789",
    "amount": 100000,
    "timestamp": "2024-01-15T10:30:00Z"
}
```

---

*API Documentation last updated: December 2024*
