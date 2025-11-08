# Postman Quick Start Guide - Healthcare API

## ⚡ Quick Setup (5 Minutes)

### Step 1: Install Postman (If Not Installed)
1. Download from https://www.postman.com/downloads/
2. Install and open Postman

### Step 2: Import Collection
1. Open Postman
2. Click **Import** button (top left)
3. Click **Upload Files**
4. Select: `Healthcare_API.postman_collection.json` (from this folder)
5. Click **Import**

### Step 3: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**
4. Verify your app is accessible at: `http://localhost/capstone/health-care/public`

### Step 4: Test Your First API Call

#### A. Login First (REQUIRED)
1. In Postman, expand **Healthcare System API** collection
2. Expand **Authentication** folder
3. Click **Login**
4. Edit the body with your actual credentials:
   ```json
   {
       "email": "your-email@example.com",
       "password": "your-password"
   }
   ```
5. Click **Send**
6. ✅ You should see: `"success": true`

#### B. Test Get All Prenatal Records
1. Expand **Prenatal Records** folder
2. Click **Get All Prenatal Records**
3. Click **Send**
4. ✅ You should see a list of records or an empty array

---

## 🎯 Testing Workflow

### Test Each Endpoint in This Order:

```
1. Login ✅
   ↓
2. Get All Records ✅
   ↓
3. Create New Record ✅
   ↓
4. Get Single Record ✅
   ↓
5. Update Record ✅
   ↓
6. Delete Record ✅
```

---

## 📋 Available API Endpoints

### Prenatal Records
```
GET    /api/prenatal-records       - Get all records
GET    /api/prenatal-records/{id}  - Get single record
POST   /api/prenatal-records       - Create new record
PUT    /api/prenatal-records/{id}  - Update record
DELETE /api/prenatal-records/{id}  - Delete record
```

### Prenatal Checkups
```
GET    /api/prenatal-checkups       - Get all checkups
GET    /api/prenatal-checkups/{id}  - Get single checkup
POST   /api/prenatal-checkups       - Create new checkup
PUT    /api/prenatal-checkups/{id}  - Update checkup
DELETE /api/prenatal-checkups/{id}  - Delete checkup
```

---

## 🔧 Common Issues

### Issue 1: "401 Unauthorized"
**Solution**: You forgot to login! Run the Login request first.

### Issue 2: "404 Not Found"
**Solution**:
- Make sure XAMPP is running
- Check your base_url is correct: `http://localhost/capstone/health-care/public`

### Issue 3: "500 Internal Server Error"
**Solution**:
- Check Laravel logs: `storage/logs/laravel.log`
- Make sure database is connected
- Run: `php artisan config:clear`

### Issue 4: Cookies Not Working
**Solution**:
1. Go to Postman Settings (⚙️ icon)
2. General → Enable "Automatically follow redirects"
3. Cookies → Enable cookies
4. Re-run the Login request

---

## 📝 Sample Request Bodies

### Create Prenatal Record
```json
{
    "patient_id": 1,
    "lmp": "2024-01-01",
    "edc": "2024-10-08",
    "gravida": 2,
    "para": 1,
    "abortion": 0,
    "living_children": 1,
    "blood_type": "O+",
    "rh_factor": "Positive"
}
```

### Create Prenatal Checkup
```json
{
    "prenatal_record_id": 1,
    "checkup_date": "2024-11-10",
    "weight": 65.5,
    "blood_pressure": "120/80",
    "fundal_height": 25,
    "fetal_heart_rate": 140,
    "status": "upcoming"
}
```

### Update Record (Partial)
```json
{
    "gravida": 3,
    "para": 2
}
```

---

## 🎓 Tips

1. **Save Responses**: Click "Save Response" to create examples
2. **Use Variables**: Edit collection variables for different environments
3. **Add Tests**: Use the Tests tab to validate responses automatically
4. **Organize**: Create folders for different modules (Patients, Immunization, etc.)

---

## 📚 More Help?

- Read the full guide: `POSTMAN_API_TESTING_GUIDE.md`
- Check API routes: `php artisan route:list --path=api`
- View logs: `storage/logs/laravel.log`

---

**Happy Testing! 🚀**
