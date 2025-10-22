# 📁 When to Use Repositories - Complete Guide

**Date:** October 3, 2025

---

## ❓ Your Question: "When will I use a Repositories folder?"

**Short Answer:** When you have **complex database queries** that you need to reuse in multiple places!

**For your rural health center:** You **might not need Repositories yet**. Services are enough for now!

---

## 🎯 What is a Repository?

**Repository** = A class that handles **ONLY database queries**. Nothing else!

### Simple Definition:

```
Repository = "Database Question Answerer"
```

**Examples of questions:**
- "Give me all active patients"
- "Find patients with high-risk pregnancies"
- "Get children due for vaccination this week"
- "Search patients by name or ID"

---

## 🏗️ Architecture Layers Explained

Let me show you the **3-layer architecture**:

```
┌─────────────────────────────────────┐
│   CONTROLLER (Coordination)         │  ← Receives request, returns response
│   "What should happen?"              │
└─────────────┬───────────────────────┘
              │
              ↓
┌─────────────────────────────────────┐
│   SERVICE (Business Logic)           │  ← Decides what to do
│   "How should we do it?"             │
└─────────────┬───────────────────────┘
              │
              ↓
┌─────────────────────────────────────┐
│   REPOSITORY (Database Queries)      │  ← Gets/saves data
│   "Where is the data?"               │
└─────────────────────────────────────┘
              │
              ↓
┌─────────────────────────────────────┐
│   DATABASE                           │
└─────────────────────────────────────┘
```

---

## 📊 When Do You NEED Repositories?

### ✅ Use Repository When:

1. **Complex Queries Used Multiple Times**
   ```php
   // This query is used in 5 different places:
   $patients = Patient::with(['prenatalRecords' => function($q) {
       $q->where('is_active', true)
         ->where('status', '!=', 'completed')
         ->orderBy('created_at', 'desc');
   }])
   ->whereHas('prenatalRecords', function($q) {
       $q->where('is_active', true);
   })
   ->orderBy('name')
   ->get();
   ```

   **Problem:** If query changes, must update 5 places! ❌

2. **Custom Search Logic**
   ```php
   // Searching patients by name, ID, or contact number
   $query = Patient::query();

   if ($search) {
       $query->where(function($q) use ($search) {
           $q->where('name', 'LIKE', "%{$search}%")
             ->orWhere('formatted_patient_id', 'LIKE', "%{$search}%")
             ->orWhere('contact', 'LIKE', "%{$search}%");
       });
   }

   if ($status) {
       $query->where('status', $status);
   }

   // This is repeated in many controllers!
   ```

3. **Advanced Filtering**
   ```php
   // Filter patients by multiple criteria
   - Age range (18-35 years old)
   - Has active prenatal record
   - High-risk status
   - Last checkup > 30 days ago
   - Lives in specific barangay
   ```

4. **Reports with Complex Joins**
   ```php
   // Get statistics for monthly report
   DB::table('patients')
     ->join('prenatal_records', ...)
     ->join('prenatal_checkups', ...)
     ->where(...)
     ->groupBy(...)
     ->having(...)
     ->get();

   // This is complex and used in reports!
   ```

---

## 🚫 When Do You NOT Need Repositories?

### ❌ Don't Use Repository When:

1. **Simple CRUD Operations**
   ```php
   // Too simple for repository!
   Patient::create($data);
   Patient::find($id);
   Patient::update($data);
   Patient::delete($id);
   ```

2. **Query Used Only Once**
   ```php
   // If this query is only in one place, keep it there!
   $patients = Patient::where('age', '>', 18)->get();
   ```

3. **Small Project (like yours!)**
   - Your rural health center is not Facebook-scale
   - Services are enough for most cases
   - Don't over-engineer!

---

## 📝 Real Examples from YOUR Project

### Example 1: Should You Use Repository for Patients?

**Current: Service Only (Good for now!)**
```php
// PatientService.php
class PatientService
{
    public function createPatient(array $data)
    {
        return Patient::create($data);  // Simple query, no repository needed
    }

    public function getActivePatients()
    {
        return Patient::where('is_active', true)
            ->orderBy('name')
            ->get();  // Simple query, no repository needed
    }
}
```

**When to add PatientRepository:**
```php
// ONLY add repository when you have complex searches like this:

// PatientRepository.php
class PatientRepository
{
    public function searchWithFilters(array $filters)
    {
        $query = Patient::with(['prenatalRecords', 'childRecords']);

        // Complex search logic (used in 5+ places)
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$search}%")
                  ->orWhere('contact', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['age_min'])) {
            $query->where('age', '>=', $filters['age_min']);
        }

        if (isset($filters['age_max'])) {
            $query->where('age', '<=', $filters['age_max']);
        }

        if (isset($filters['has_prenatal_record'])) {
            $query->whereHas('prenatalRecords', function($q) {
                $q->where('is_active', true);
            });
        }

        if (isset($filters['barangay'])) {
            $query->where('address', 'LIKE', "%{$filters['barangay']}%");
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function getHighRiskPregnancies()
    {
        // Complex query used in dashboard, reports, and alerts
        return Patient::whereHas('prenatalRecords', function($q) {
            $q->where('is_active', true)
              ->where('status', 'high-risk');
        })
        ->with(['prenatalRecords' => function($q) {
            $q->where('is_active', true)
              ->with('latestCheckup');
        }])
        ->get();
    }
}
```

---

### Example 2: PrenatalCheckup - Do You Need Repository?

**Current: Service Only (Good!)**
```php
// PrenatalCheckupService.php
class PrenatalCheckupService
{
    public function createCheckup(array $data)
    {
        // Business logic + simple database operations
        return PrenatalCheckup::create($data);
    }
}
```

**When to add PrenatalCheckupRepository:**
```php
// ONLY if you have complex queries like:

// PrenatalCheckupRepository.php
class PrenatalCheckupRepository
{
    public function getUpcomingCheckupsForNextWeek()
    {
        // Complex query used in dashboard, SMS reminders, and reports
        return PrenatalCheckup::with(['patient', 'prenatalRecord'])
            ->where('status', 'upcoming')
            ->whereBetween('checkup_date', [now(), now()->addWeek()])
            ->whereHas('prenatalRecord', function($q) {
                $q->where('is_active', true)
                  ->where('status', '!=', 'completed');
            })
            ->orderBy('checkup_date')
            ->get();
    }

    public function getMissedCheckupsRequiringFollowUp()
    {
        // Complex query used in multiple places
        return PrenatalCheckup::where('status', 'missed')
            ->where('checkup_date', '>', now()->subMonth())
            ->whereHas('patient', function($q) {
                $q->where('is_active', true);
            })
            ->whereDoesntHave('rescheduledCheckup')
            ->with(['patient', 'prenatalRecord'])
            ->get();
    }
}
```

---

## 🎯 Decision Tree: Service vs Repository

```
┌─────────────────────────────────────┐
│  Do you need to get/save data?     │
└─────────────┬───────────────────────┘
              │
              ↓
        ┌─────────────┐
        │  Is query   │
        │   simple?   │
        └──────┬──────┘
               │
       ┌───────┴───────┐
       │               │
      YES             NO
       │               │
       ↓               ↓
   ┌────────┐    ┌──────────┐
   │ Put in │    │ Is query │
   │Service │    │  used in │
   │        │    │ multiple │
   │  DONE  │    │ places?  │
   └────────┘    └─────┬────┘
                       │
                ┌──────┴──────┐
                │             │
               YES           NO
                │             │
                ↓             ↓
          ┌──────────┐   ┌────────┐
          │ Create   │   │ Keep   │
          │Repository│   │  in    │
          │          │   │Service │
          │   USE    │   │        │
          │   THIS!  │   │  DONE  │
          └──────────┘   └────────┘
```

---

## 📚 Code Examples: Service vs Service + Repository

### Scenario 1: Simple CRUD (Service Only - No Repository)

```php
// PatientService.php
class PatientService
{
    public function createPatient(array $data)
    {
        DB::beginTransaction();
        try {
            // Business logic
            $data['age'] = Carbon::parse($data['birthdate'])->age;
            $data['formatted_patient_id'] = $this->generatePatientId();

            // Simple database operation (no repository needed)
            $patient = Patient::create($data);

            if (isset($data['create_prenatal_record'])) {
                $this->createInitialPrenatalRecord($patient, $data);
            }

            DB::commit();
            return $patient;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generatePatientId()
    {
        // Simple query (no repository needed)
        $lastPatient = Patient::latest('id')->first();
        $lastId = $lastPatient ? intval(substr($lastPatient->formatted_patient_id, 3)) : 0;
        return 'PAT' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }
}
```

**When to use:** Your current project size! ✅

---

### Scenario 2: Complex Queries (Service + Repository)

```php
// PatientRepository.php (NEW - only for complex queries)
class PatientRepository
{
    public function searchWithFilters(array $filters)
    {
        // This complex query is used in:
        // - Web search
        // - API search
        // - Export functionality
        // - Reports
        // So we put it in repository!

        $query = Patient::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$search}%")
                  ->orWhere('contact', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['age_range'])) {
            [$min, $max] = explode('-', $filters['age_range']);
            $query->whereBetween('age', [$min, $max]);
        }

        if (!empty($filters['has_high_risk_pregnancy'])) {
            $query->whereHas('prenatalRecords', function($q) {
                $q->where('is_active', true)
                  ->where('status', 'high-risk');
            });
        }

        return $query->with(['prenatalRecords', 'childRecords'])
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getPatientStatistics()
    {
        // Complex aggregation used in dashboard and reports
        return [
            'total' => Patient::count(),
            'active_pregnancies' => Patient::whereHas('prenatalRecords', function($q) {
                $q->where('is_active', true);
            })->count(),
            'high_risk' => Patient::whereHas('prenatalRecords', function($q) {
                $q->where('status', 'high-risk');
            })->count(),
            'by_age_group' => Patient::selectRaw('
                CASE
                    WHEN age < 18 THEN "under_18"
                    WHEN age BETWEEN 18 AND 35 THEN "18_35"
                    ELSE "over_35"
                END as age_group,
                COUNT(*) as count
            ')->groupBy('age_group')->get(),
        ];
    }
}

// PatientService.php (Uses Repository)
class PatientService
{
    protected $patientRepository;

    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function searchPatients(array $filters)
    {
        // Service uses repository for complex query
        return $this->patientRepository->searchWithFilters($filters);
    }

    public function createPatient(array $data)
    {
        // Service still handles business logic
        DB::beginTransaction();
        try {
            $data['age'] = Carbon::parse($data['birthdate'])->age;
            $data['formatted_patient_id'] = $this->generatePatientId();

            // Simple create - no repository needed
            $patient = Patient::create($data);

            DB::commit();
            return $patient;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

**When to use:** Large projects with complex searches and reporting! ⚠️

---

## 🎯 For YOUR Rural Health Center Project

### Current Recommendation: **Service Only (No Repository Yet)** ✅

**Why?**
1. ✅ Your queries are mostly simple
2. ✅ Not much duplication yet
3. ✅ Small team (easier to maintain)
4. ✅ Don't over-engineer!

### When to Add Repositories Later:

Add `PatientRepository` when you have:
- [ ] Patient search used in 5+ places
- [ ] Complex filtering (age, address, risk level, etc.)
- [ ] Need same query in Web + API + Reports

Add `PrenatalCheckupRepository` when you have:
- [ ] Complex queries for upcoming checkups
- [ ] Advanced filtering for reports
- [ ] SMS reminder queries reused everywhere

Add `ImmunizationRepository` when you have:
- [ ] Complex vaccine schedule queries
- [ ] Missed vaccine calculations reused
- [ ] Reporting queries with multiple joins

---

## 📁 Folder Structure: Now vs Later

### Current (Good for now!):
```
app/
├── Http/
│   ├── Controllers/
│   │   └── PatientController.php (100 lines)
│   └── Requests/
│       └── StorePatientRequest.php
│
└── Services/
    └── PatientService.php (200 lines)

Simple! Easy to maintain! ✅
```

### Later (When you grow):
```
app/
├── Http/
│   ├── Controllers/
│   │   └── PatientController.php (100 lines)
│   └── Requests/
│       └── StorePatientRequest.php
│
├── Services/
│   └── PatientService.php (150 lines - less code!)
│
└── Repositories/               ← ADD THIS LATER
    └── PatientRepository.php (100 lines - complex queries)

More organized for complex queries! ✅
```

---

## 🎯 Summary

### When to Use Repository:

| Situation | Use Repository? | Reason |
|-----------|----------------|---------|
| Simple Patient::create() | ❌ NO | Too simple |
| Simple Patient::find($id) | ❌ NO | Too simple |
| Complex search (5 filters) | ✅ YES | Reused in many places |
| Report with 3 joins | ✅ YES | Complex + reused |
| One-time complex query | ❌ NO | Not reused |
| Your current project | ❌ NO (yet) | Not complex enough |
| Facebook-scale project | ✅ YES | Very complex queries |

### Your Rural Health Center:

**Phase 2 (Now - After SMS):**
- ✅ Use Services only
- ✅ Keep it simple
- ✅ Easy to maintain

**Phase 4 (Later - Maybe next year):**
- ⚠️ Add Repositories if:
  - Queries become very complex
  - Same query used 5+ times
  - API + Web + Reports need same data

---

## 💡 Key Takeaway

**Repository = Optional optimization for complex queries**

**For your project:**
- ✅ **NOW:** Services are enough
- ⏳ **LATER:** Add repositories when queries get complex
- 🚫 **DON'T:** Over-engineer from the start

**Focus on:** SMS Integration (Monday) → Phase 3 Refactoring → Then decide on repositories!

---

**Simple rule:** If your Service methods are clean and under 300 lines, you don't need repositories yet! ✅

