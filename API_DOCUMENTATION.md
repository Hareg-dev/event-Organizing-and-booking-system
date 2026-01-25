# EventHub API Documentation

## 📋 Table of Contents
1. [Authentication API](#authentication-api)
2. [Events API](#events-api)
3. [Bookings API](#bookings-api)
4. [Admin API](#admin-api)
5. [Reviews API](#reviews-api)
6. [Favorites API](#favorites-api)
7. [Error Handling](#error-handling)
8. [Response Formats](#response-formats)

---

## 🔐 Authentication API

**Base URL:** `/api/auth.php`

### Check Authentication Status
```http
GET /api/auth.php?action=check
```

**Response:**
```json
{
  "logged_in": true,
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "user"
  }
}
```

### Login
```http
POST /api/auth.php
Content-Type: application/json

{
  "action": "login",
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "user"
  }
}
```

### Register
```http
POST /api/auth.php
Content-Type: application/json

{
  "action": "register",
  "email": "newuser@example.com",
  "password": "password123",
  "first_name": "Jane",
  "last_name": "Smith",
  "phone": "1234567890",
  "role": "user"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "user_id": 5
}
```

### Logout
```http
POST /api/auth.php
Content-Type: application/json

{
  "action": "logout"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

## 🎉 Events API

**Base URL:** `/api/events.php`

### Get All Events
```http
GET /api/events.php
GET /api/events.php?category=music&city=New+York&search=concert&limit=10
```

**Query Parameters:**
- `category` (optional): Filter by category name
- `city` (optional): Filter by city
- `search` (optional): Search in title and description
- `limit` (optional): Limit number of results

**Response:**
```json
[
  {
    "id": 1,
    "title": "Summer Music Festival",
    "description": "Amazing outdoor music festival",
    "date_start": "2024-07-15 18:00:00",
    "date_end": "2024-07-15 23:00:00",
    "location": "Central Park",
    "city": "New York",
    "image_url": "https://example.com/image.jpg",
    "category_name": "Music",
    "organizer_name": "John Organizer",
    "is_featured": 1,
    "status": "published",
    "ticket_types": [
      {
        "id": 1,
        "name": "General Admission",
        "price": 50.00,
        "quantity": 100,
        "available": 85
      }
    ]
  }
]
```

### Get Event by ID
```http
GET /api/events.php?id=1
```

**Response:**
```json
{
  "id": 1,
  "title": "Summer Music Festival",
  "description": "Amazing outdoor music festival with top artists",
  "date_start": "2024-07-15 18:00:00",
  "date_end": "2024-07-15 23:00:00",
  "location": "Central Park",
  "city": "New York",
  "image_url": "https://example.com/image.jpg",
  "category_name": "Music",
  "organizer_name": "John Organizer",
  "organizer_email": "organizer@example.com",
  "is_featured": 1,
  "status": "published",
  "created_at": "2024-01-15 10:00:00",
  "ticket_types": [
    {
      "id": 1,
      "name": "General Admission",
      "price": 50.00,
      "quantity": 100,
      "available": 85,
      "description": "General admission ticket"
    },
    {
      "id": 2,
      "name": "VIP",
      "price": 150.00,
      "quantity": 20,
      "available": 15,
      "description": "VIP access with premium amenities"
    }
  ]
}
```

### Get Featured Events
```http
GET /api/events.php?featured=1&limit=6
```

### Get Categories
```http
GET /api/events.php?categories=1
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Music",
    "description": "Music events and concerts"
  },
  {
    "id": 2,
    "name": "Sports",
    "description": "Sports events and competitions"
  }
]
```

### Create Event (Requires organizer/admin role)
```http
POST /api/events.php
Content-Type: application/json

{
  "title": "New Event",
  "description": "Event description",
  "date_start": "2024-12-31 20:00:00",
  "date_end": "2024-12-31 23:00:00",
  "location": "Event Venue",
  "city": "City Name",
  "category_id": 1,
  "image_url": "https://example.com/image.jpg",
  "status": "published",
  "is_featured": 0,
  "ticket_types": [
    {
      "name": "General",
      "price": 50.00,
      "quantity": 100,
      "description": "General admission"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Event created successfully",
  "event_id": 15
}
```

### Update Event
```http
PUT /api/events.php?id=1
Content-Type: application/json

{
  "title": "Updated Event Title",
  "description": "Updated description"
}
```

### Delete Event
```http
DELETE /api/events.php?id=1
```

---

## 🎫 Bookings API

**Base URL:** `/api/bookings.php`

### Create Booking
```http
POST /api/bookings.php
Content-Type: application/json

{
  "event_id": 1,
  "ticket_type_id": 1,
  "quantity": 2,
  "payment_method": "card"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "booking_id": 25,
  "total_amount": 100.00
}
```

### Get User Bookings
```http
GET /api/bookings.php?user_id=1
```

**Response:**
```json
[
  {
    "id": 25,
    "event_title": "Summer Music Festival",
    "event_date": "2024-07-15 18:00:00",
    "ticket_type_name": "General Admission",
    "quantity": 2,
    "total_amount": 100.00,
    "status": "confirmed",
    "booking_date": "2024-01-20 14:30:00",
    "payment_method": "card"
  }
]
```

### Get Booking by ID
```http
GET /api/bookings.php?id=25
```

### Get Event Bookings
```http
GET /api/bookings.php?event_id=1
```

### Get Booking Statistics
```http
GET /api/bookings.php?stats=1&event_id=1
GET /api/bookings.php?user_stats=1&user_id=1
```

### Confirm Booking
```http
PUT /api/bookings.php?id=25
Content-Type: application/json

{
  "action": "confirm"
}
```

### Cancel Booking
```http
PUT /api/bookings.php?id=25
Content-Type: application/json

{
  "action": "cancel"
}
```

### Generate Ticket
```http
POST /api/bookings.php
Content-Type: application/json

{
  "action": "generate_ticket",
  "booking_id": 25
}
```

### Process Payment
```http
POST /api/bookings.php
Content-Type: application/json

{
  "action": "process_payment",
  "booking_id": 25,
  "payment_method": "card",
  "card_number": "****-****-****-1234"
}
```

---

## 👑 Admin API

**Base URL:** `/api/admin.php`
**Authentication:** Requires admin role

### Get System Statistics
```http
GET /api/admin.php?stats=1
```

**Response:**
```json
{
  "total_users": 150,
  "total_organizers": 25,
  "total_events": 45,
  "total_bookings": 320,
  "total_revenue": 15750.00,
  "recent_users": [
    {
      "id": 5,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane@example.com",
      "role": "user",
      "created_at": "2024-01-20 10:00:00"
    }
  ],
  "recent_events": [
    {
      "id": 10,
      "title": "New Event",
      "organizer_name": "John Organizer",
      "created_at": "2024-01-19 15:30:00"
    }
  ]
}
```

### Get All Users
```http
GET /api/admin.php?users=1
```

**Response:**
```json
[
  {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "1234567890",
    "created_at": "2024-01-01 12:00:00",
    "last_login": "2024-01-20 09:00:00"
  }
]
```

### Get All Events (Admin View)
```http
GET /api/admin.php?events=1
```

### Change User Role
```http
PUT /api/admin.php
Content-Type: application/json

{
  "action": "change_user_role",
  "user_id": 5,
  "role": "organizer"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User role updated successfully"
}
```

### Delete User
```http
DELETE /api/admin.php?user_id=5
```

### Delete Event
```http
DELETE /api/admin.php?event_id=10
```

---

## ⭐ Reviews API

**Base URL:** `/api/reviews.php`

### Get Event Reviews
```http
GET /api/reviews.php?event_id=1
```

**Response:**
```json
[
  {
    "id": 1,
    "user_name": "John Doe",
    "rating": 5,
    "comment": "Amazing event! Highly recommended.",
    "created_at": "2024-01-20 16:00:00"
  }
]
```

### Get User Reviews
```http
GET /api/reviews.php?user_id=1
```

### Create Review (Requires authentication)
```http
POST /api/reviews.php
Content-Type: application/json

{
  "event_id": 1,
  "rating": 5,
  "comment": "Great event, well organized!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Review created successfully",
  "review_id": 15
}
```

### Delete Review (Requires authentication - own reviews only)
```http
DELETE /api/reviews.php?id=15
```

---

## ❤️ Favorites API

**Base URL:** `/api/favorites.php`
**Authentication:** Required for all endpoints

### Get User Favorites
```http
GET /api/favorites.php
GET /api/favorites.php?user_id=1
```

**Response:**
```json
[
  {
    "event_id": 1,
    "event_title": "Summer Music Festival",
    "event_date": "2024-07-15 18:00:00",
    "event_image": "https://example.com/image.jpg",
    "added_at": "2024-01-20 10:00:00"
  }
]
```

### Check if Event is Favorite
```http
GET /api/favorites.php?check=1&event_id=1
```

**Response:**
```json
{
  "is_favorite": true
}
```

### Add to Favorites
```http
POST /api/favorites.php
Content-Type: application/json

{
  "event_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Event added to favorites"
}
```

### Remove from Favorites
```http
DELETE /api/favorites.php?event_id=1
```

**Response:**
```json
{
  "success": true,
  "message": "Event removed from favorites"
}
```

---

## 🚨 Error Handling

All API endpoints return consistent error responses:

### Authentication Errors
```json
{
  "success": false,
  "message": "Authentication required"
}
```
**HTTP Status:** 401 Unauthorized

### Authorization Errors
```json
{
  "success": false,
  "message": "Admin access required"
}
```
**HTTP Status:** 403 Forbidden

### Validation Errors
```json
{
  "success": false,
  "message": "Email and password are required"
}
```
**HTTP Status:** 400 Bad Request

### Not Found Errors
```json
{
  "error": "Event not found"
}
```
**HTTP Status:** 404 Not Found

### Server Errors
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Database connection failed"
}
```
**HTTP Status:** 500 Internal Server Error

---

## 📊 Response Formats

### Success Response Format
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* response data */ }
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information (optional)"
}
```

### List Response Format
```json
[
  { /* item 1 */ },
  { /* item 2 */ },
  { /* item 3 */ }
]
```

---

## 🔧 Common Headers

All API requests should include:
```http
Content-Type: application/json
```

All API responses include:
```http
Content-Type: application/json
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## 📝 Notes

1. **Authentication**: Session-based authentication is used. Login to establish a session.
2. **Roles**: Three user roles exist - `user`, `organizer`, `admin`
3. **Permissions**: 
   - Users can view events, create bookings, add reviews/favorites
   - Organizers can create/manage their own events
   - Admins have full system access
4. **Rate Limiting**: No rate limiting currently implemented
5. **Pagination**: Use `limit` parameter for basic pagination
6. **Timestamps**: All timestamps are in `YYYY-MM-DD HH:MM:SS` format
7. **Currency**: All prices are in USD with 2 decimal places

---

## 🧪 Testing

Use the provided test files:
- `test-api.html` - Interactive API testing interface
- `test-functionality.html` - Comprehensive functionality tests

**Test Accounts:**
- Admin: `admin@eventhub.com` / `admin123`
- Organizer: `organizer@eventhub.com` / `organizer123`
- User: `user@eventhub.com` / `user123`