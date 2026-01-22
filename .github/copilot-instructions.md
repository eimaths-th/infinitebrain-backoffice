# Infinite Brain Back Office - AI Coding Instructions

## Project Overview
Laravel 9 back-office system for **Infinite Brain** tutoring school (Thai language). Manages students, parents, receipts, course registrations, employee attendance, and financial documents.

## Tech Stack
- **Backend:** Laravel 9, PHP 8.0+, MySQL (XAMPP)
- **Frontend:** Blade templates, Bootstrap 5, jQuery, DataTables, Chart.js, Select2
- **Excel:** Maatwebsite/Excel 3.x for import/export
- **PDF:** DomPDF, TCPDF
- **Auth:** Laravel Sanctum + custom multi-guard (parent/admin)

## Architecture Patterns

### Model Naming Convention
Models use mixed conventions - follow existing patterns:
- Underscore style: `Receipt_School`, `Receipt_RunningNumber`, `Parent_RunningNumber`
- CamelCase: `PaymentVoucher`, `SchoolPrice`, `ParentRegister`
- Custom table names via `protected $table` when class name differs

### Controller Structure
```
app/Http/Controllers/
├── Api/                    # API endpoints (DailyIncomeController)
├── Auth/                   # Authentication controllers
└── [Feature]Controller.php # Feature-based controllers
```

### View Layout
All admin views extend `layouts.main_template`:
```blade
@extends('layouts.main_template')
@section('style') <!-- CSS/JS includes --> @endsection
@section('content') <!-- Page content --> @endsection  
@section('script') <!-- Page-specific JS --> @endsection
```

### Sidebar Menu
Add new menu items in `resources/views/layouts/sidebar.blade.php`. Uses user level-based access:
- Level 0: Super Admin
- Level 1: Admin  
- Level 2: Staff
- Level 3: Parent (limited access)

## Key Domain Models

### Core Relationships
```
Student → ParentRegister (belongsTo via 'pid')
Student → Receipt_School (hasMany via 'pid')
Student → SchoolPrice (hasMany via 'student_id')
Receipt_School → SchoolPrice (hasOne via 'rid')
```

### Running Numbers Pattern
Auto-generated document numbers use dedicated models:
- `Receipt_RunningNumber` - Receipt numbers
- `Receipt_School_RunningNumber` - School receipt numbers
- `Parent_RunningNumber` - Parent registration IDs

Example generation:
```php
PaymentVoucher::generateVoucherNo() // Returns: PV-2026-0001
```

## Common Development Tasks

### Creating New Feature (CRUD + Approval)
1. Migration: `php artisan make:migration create_[table]_table`
2. Model: `app/Models/[Model].php` with `$fillable`, relationships, status constants
3. Controller: `app/Http/Controllers/[Model]Controller.php`
4. Routes: Add to `routes/web.php` inside auth middleware group
5. Views: Create folder `resources/views/[feature]/` with index, create, edit, show
6. Sidebar: Add menu in `sidebar.blade.php`

### Excel Export Pattern
```php
// app/Exports/[Model]Export.php
use Maatwebsite\Excel\Concerns\FromView;
class ModelExport implements FromView {
    public function view(): View {
        return view('model.export_excel', ['data' => $this->data]);
    }
}
```

### Activity Logging
Use `App\Helpers\LogActivity::modelEvent()` for audit trails:
```php
LogActivity::modelEvent($model, 'created', null, $model->toArray());
```

## Database Notes
- Production routes use `php artisan route:cache` 
- Migrations run with `php artisan migrate` (confirms in production)
- Use raw dates carefully - many date fields are strings, not Carbon
- Thai dates use `phattarachai/thaidate` package

## Frontend Patterns

### DataTables
Most list views use server-side DataTables via `yajra/laravel-datatables`:
```javascript
$('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("data.route") }}'
});
```

### Form Validation
Client-side with Bootstrap 5 validation + Laravel server-side:
```blade
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </div>
@endif
```

### CDN Dependencies
Use cdnjs.cloudflare.com for external libraries (avoid deprecated rawgit.com):
- jQuery InputMask: `cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/`
- Select2, DataTables, Chart.js loaded in main_template

## API Development
APIs live in `routes/api.php`. Income reporting example:
```php
Route::prefix('income')->group(function () {
    Route::get('/daily', [DailyIncomeController::class, 'dailySummary']);
    Route::get('/today', [DailyIncomeController::class, 'todaySummary']);
});
```

## Thai Language Support
- Views contain Thai text directly in Blade
- Status labels use Thai: `'pending' => 'รออนุมัติ'`
- Number formatting: `number_format($amount, 2)` + `บาท`
