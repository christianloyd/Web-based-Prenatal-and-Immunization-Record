# Rate Limiting Strategy

## Overview

This application implements comprehensive rate limiting to protect against abuse, brute-force attacks, and ensure fair resource usage.

## Rate Limits by Endpoint Type

### 1. Guest Routes (Unauthenticated)

**Login Endpoints:**
- **GET /login**: 10 requests per minute per IP
- **POST /login**: 5 requests per minute per IP
  - Additional manual rate limiting in AuthController: 5 attempts per username+IP per 5 minutes
  - Lockout period: 5 minutes after 5 failed attempts

**Purpose:** Prevent brute-force login attacks

### 2. OAuth Routes

**Google OAuth:**
- **GET /google/auth**: 10 requests per minute per IP
- **GET /google/callback**: 10 requests per minute per IP
- **POST /google/disconnect**: 10 requests per minute per user (requires auth)

**Purpose:** Prevent OAuth abuse and unauthorized access attempts

### 3. Authenticated Web Routes

**General Authenticated Routes:**
- All authenticated routes: **60 requests per minute per user**

This includes:
- Dashboard access
- Notifications
- Logout

**Purpose:** Fair usage and protection against automated scraping

### 4. Role-Specific Routes

**Midwife Routes (`/midwife/*`):**
- **API throttle limit**: 60 requests per minute per user
- Includes all CRUD operations for:
  - Patients, prenatal records, child records
  - Immunizations, vaccines
  - Appointments, reports
  - User management
  - Cloud backups

**BHW Routes (`/bhw/*`):**
- **API throttle limit**: 60 requests per minute per user
- Includes all CRUD operations for:
  - Patients, prenatal records, child records
  - Immunizations, appointments
  - Reports

**Purpose:** Prevent abuse of privileged operations

### 5. API Routes

**API Endpoints (`/api/*`):**
- All API routes: **60 requests per minute per user**
- Includes:
  - `/api/user` (Sanctum authenticated)
  - `/api/prenatal-records`
  - `/api/prenatal-checkups`

**Purpose:** Prevent API abuse and ensure fair resource distribution

## Rate Limit Implementation

### Middleware Configuration

```php
// routes/web.php

// Guest routes - strict limits
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::post('/login', ...)->middleware('throttle:5,1'); // Extra strict on POST
});

// Authenticated routes - standard limits
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // Dashboard, notifications, etc.
});

// Role-specific routes - API throttle
Route::middleware(['auth', 'role:midwife', 'throttle:api'])->group(function () {
    // Midwife operations
});
```

### Throttle Format

- `throttle:X,Y` where:
  - **X** = Maximum number of requests
  - **Y** = Time window in minutes

- `throttle:api` uses Laravel's default API rate limit (60 requests per minute)

## Rate Limit Headers

Responses include the following headers:

- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Unix timestamp when limit resets

## Handling Rate Limit Exceeded

When rate limit is exceeded, Laravel returns:

**Status Code:** `429 Too Many Requests`

**Response:**
```json
{
    "message": "Too Many Attempts."
}
```

**Retry-After Header:** Indicates seconds until limit resets

## Best Practices

### For Developers

1. **Check rate limit headers** in API responses
2. **Implement exponential backoff** for automated tasks
3. **Cache data locally** when possible
4. **Use batch operations** instead of multiple individual requests

### For System Administrators

1. **Monitor rate limit hits** in logs
2. **Adjust limits** based on usage patterns
3. **Whitelist specific IPs** for trusted automated systems if needed
4. **Review failed attempts** regularly for security incidents

## Security Considerations

### Login Protection

- **Username + IP combination** for rate limiting (prevents username enumeration)
- **5-minute lockout** after 5 failed attempts
- **Clear rate limiter** on successful login
- **Deactivated accounts** cannot authenticate

### API Protection

- **Per-user rate limiting** prevents single user abuse
- **Different limits for read/write** can be configured if needed
- **Authenticated requests only** for sensitive endpoints

## Customization

To adjust rate limits, modify values in:

1. **routes/web.php** - Web route rate limits
2. **routes/api.php** - API route rate limits
3. **config/cache.php** - Cache driver for rate limiter storage

## Monitoring

Rate limit events are logged automatically. Check logs for:

```bash
# View rate limit logs
tail -f storage/logs/laravel.log | grep -i "rate"
```

## Future Enhancements

Consider implementing:

1. **Dynamic rate limiting** based on user tier
2. **Separate limits for read vs write** operations
3. **Redis-based rate limiting** for better performance
4. **Rate limit analytics dashboard**
5. **Automatic IP blocking** for repeated violators

## Testing Rate Limits

### Manual Testing

```bash
# Test login rate limit
for i in {1..6}; do
  curl -X POST http://localhost/login \
    -d "username=test&password=wrong" \
    -w "\nStatus: %{http_code}\n\n"
done

# Test API rate limit
for i in {1..65}; do
  curl -H "Authorization: Bearer TOKEN" \
    http://localhost/api/prenatal-records \
    -w "\nStatus: %{http_code}\n"
done
```

### Automated Testing

Add feature tests in `tests/Feature/RateLimitTest.php`:

```php
public function test_login_rate_limit()
{
    for ($i = 0; $i < 6; $i++) {
        $response = $this->post('/login', [
            'username' => 'test',
            'password' => 'wrong'
        ]);
    }

    $this->assertEquals(429, $response->status());
}
```

## Support

For issues with rate limiting:
- Check rate limit headers in responses
- Review logs for specific error messages
- Contact system administrator for limit adjustments
