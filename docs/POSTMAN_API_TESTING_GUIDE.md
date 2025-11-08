# Complete Guide: Testing Laravel API with Postman

## Prerequisites

1. **Postman installed** - Download from [postman.com](https://www.postman.com/downloads/)
2. **XAMPP running** - Apache and MySQL must be started
3. **Laravel app running** - Your health-care system should be accessible at `http://localhost/capstone/health-care/public`

---

## Part 1: Setting Up Your Environment

### Step 1: Create Postman Environment

1. Open Postman
2. Click **Environments** tab on the left sidebar
3. Click **+** to create new environment
4. Name it: `Healthcare Local`
5. Add these variables:

   | Variable | Initial Value | Current Value |
   |----------|--------------|---------------|
   | `base_url` | `http://localhost/capstone/health-care/public` | (same) |
   | `api_url` | `{{base_url}}/api` | (same) |
   | `token` | (leave empty - we'll set this after login) | (leave empty) |

6. Click **Save**
7. Select this environment from the dropdown in top right

---

## Part 2: Authentication Setup

### Step 2: Login to Get Session Cookie

Since your API uses `auth` middleware (session-based), you need to login first.

1. **Create new request**:
   - Click **New** → **HTTP Request**
   - Name: `Login`
   - Method: `POST`
   - URL: `{{base_url}}/login`

2. **Headers**:
   ```
   Content-Type: application/json
   Accept: application/json
   ```

3. **Body** (select **raw** and **JSON**):
   ```json
   {
       "email": "your-email@example.com",
       "password": "your-password"
   }
   ```

4. **Click Send**

5. **Expected Response**:
   ```json
   {
       "success": true,
       "redirect": "/midwife/dashboard"
   }
   ```

6. **Save the Cookie**:
   - After successful login, Postman automatically saves the session cookie
   - You can see it in **Cookies** tab below the response

---

## Part 3: Testing API Endpoints

Your current API routes (from `routes/api.php`):

### Available Endpoints:

```
GET     /api/prenatal              - Get all prenatal records
POST    /api/prenatal              - Create new prenatal record
GET     /api/prenatal/stats        - Get dashboard statistics
GET     /api/prenatal/{id}         - Get specific prenatal record
PUT     /api/prenatal/{id}         - Update prenatal record
DELETE  /api/prenatal/{id}         - Delete prenatal record
PATCH   /api/prenatal/{id}/status  - Update status only
```

**Note**: These routes currently reference a non-existent controller. We need to fix this first (see Part 5).

---

## Part 4: Testing Each Endpoint (Once Fixed)

### Test 1: Get All Prenatal Records

1. **Create new request**:
   - Name: `Get All Prenatal Records`
   - Method: `GET`
   - URL: `{{api_url}}/prenatal`

2. **Headers**:
   ```
   Accept: application/json
   ```

3. **Click Send**

4. **Expected Response**:
   ```json
   {
       "success": true,
       "data": [
           {
               "id": 1,
               "patient_id": 1,
               "patient_name": "Jane Doe",
               ...
           }
       ]
   }
   ```

### Test 2: Get Single Prenatal Record

1. **Create new request**:
   - Name: `Get Single Prenatal Record`
   - Method: `GET`
   - URL: `{{api_url}}/prenatal/1`

2. **Headers**:
   ```
   Accept: application/json
   ```

3. **Click Send**

### Test 3: Create New Prenatal Record

1. **Create new request**:
   - Name: `Create Prenatal Record`
   - Method: `POST`
   - URL: `{{api_url}}/prenatal`

2. **Headers**:
   ```
   Content-Type: application/json
   Accept: application/json
   ```

3. **Body** (raw JSON):
   ```json
   {
       "patient_id": 1,
       "lmp": "2024-01-01",
       "edc": "2024-10-08",
       "gravida": 2,
       "para": 1,
       "blood_type": "O+",
       "rh_factor": "Positive"
   }
   ```

4. **Click Send**

### Test 4: Update Prenatal Record

1. **Create new request**:
   - Name: `Update Prenatal Record`
   - Method: `PUT`
   - URL: `{{api_url}}/prenatal/1`

2. **Headers**:
   ```
   Content-Type: application/json
   Accept: application/json
   ```

3. **Body** (raw JSON):
   ```json
   {
       "gravida": 3,
       "para": 2
   }
   ```

4. **Click Send**

### Test 5: Delete Prenatal Record

1. **Create new request**:
   - Name: `Delete Prenatal Record`
   - Method: `DELETE`
   - URL: `{{api_url}}/prenatal/1`

2. **Headers**:
   ```
   Accept: application/json
   ```

3. **Click Send**

---

## Part 5: Fixing API Routes (REQUIRED)

Your current `routes/api.php` references `PrenatalController` which doesn't exist. You have two options:

### Option A: Use Existing Controllers

Update `routes/api.php` to use your existing controllers:

```php
<?php

use App\Http\Controllers\PrenatalRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Prenatal Records API
Route::middleware('auth')->prefix('prenatal-records')->group(function () {
    Route::get('/', [PrenatalRecordController::class, 'index']);
    Route::post('/', [PrenatalRecordController::class, 'store']);
    Route::get('/{id}', [PrenatalRecordController::class, 'show']);
    Route::put('/{id}', [PrenatalRecordController::class, 'update']);
    Route::delete('/{id}', [PrenatalRecordController::class, 'destroy']);
});
```

### Option B: Create API Methods in Existing Controller

Add these methods to `PrenatalRecordController.php`:

```php
// API method that returns JSON
public function apiIndex()
{
    $records = PrenatalRecord::with('patient')->get();
    return response()->json([
        'success' => true,
        'data' => $records
    ]);
}

public function apiShow($id)
{
    $record = PrenatalRecord::with('patient')->findOrFail($id);
    return response()->json([
        'success' => true,
        'data' => $record
    ]);
}

// Add similar methods for store, update, destroy
```

---

## Part 6: Common Issues and Solutions

### Issue 1: 401 Unauthorized

**Problem**: Not authenticated

**Solution**:
- Make sure you logged in first (Step 2)
- Check that cookies are being sent with requests
- In Postman, go to Settings → General → Enable "Automatically follow redirects"

### Issue 2: 419 CSRF Token Mismatch

**Problem**: Missing CSRF token for session-based auth

**Solution**:
- Add this to your API routes if using session auth:
  ```php
  Route::middleware('web')->group(function () {
      // Your routes here
  });
  ```
- OR exclude API routes from CSRF in `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->validateCsrfTokens(except: [
          'api/*'
      ]);
  })
  ```

### Issue 3: 404 Not Found

**Problem**: Routes not loaded

**Solution**:
- Make sure `api.php` is registered in `bootstrap/app.php`
- Run: `php artisan route:clear`
- Check routes exist: `php artisan route:list --path=api`

### Issue 4: CORS Error

**Problem**: Cross-origin requests blocked

**Solution**: Not needed for testing with Postman, but if building a frontend:
```bash
composer require fruitcake/laravel-cors
```

---

## Part 7: Organizing Your Postman Collection

### Create a Collection

1. Click **Collections** in left sidebar
2. Click **+** New Collection
3. Name it: `Healthcare API`
4. Add folders:
   - `Auth`
   - `Prenatal Records`
   - `Child Records`
   - `Immunization`
   - `Patients`

5. Drag your requests into appropriate folders

### Add Tests (Optional but Recommended)

In each request, go to **Tests** tab and add:

```javascript
// Test that response is 200 OK
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test that response has success field
pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
});
```

---

## Part 8: Testing Workflow

### Recommended Testing Order:

1. ✅ Login (get authenticated)
2. ✅ GET all records (verify endpoint works)
3. ✅ POST create new record
4. ✅ GET single record (verify it was created)
5. ✅ PUT update record
6. ✅ GET single record (verify it was updated)
7. ✅ DELETE record
8. ✅ GET all records (verify it was deleted)

---

## Quick Reference Commands

### Laravel Commands

```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list

# View API routes only
php artisan route:list --path=api

# Clear route cache specifically
php artisan route:clear
```

### Postman Shortcuts

- `Ctrl + Enter` - Send request
- `Ctrl + S` - Save request
- `Ctrl + N` - New request
- `Ctrl + E` - Manage environments

---

## Next Steps

1. Fix the API routes (Part 5)
2. Test authentication (Part 2)
3. Test each endpoint (Part 4)
4. Create a complete collection (Part 7)
5. Add validation tests (Part 7)

---

## Resources

- [Postman Learning Center](https://learning.postman.com/)
- [Laravel API Documentation](https://laravel.com/docs/routing#api-routes)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)

---

**Created**: 2025-11-03
**For**: Healthcare System API Testing
