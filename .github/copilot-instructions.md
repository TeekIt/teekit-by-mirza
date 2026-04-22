# Teekit — Copilot Development Guidelines

> **Copilot you are a Full Stack AI assistant with 10 years of experience in this specific project. These guidelines MUST be followed before developing any new module, feature, controller, action, service, Livewire component, or blade view in this project.**

---

## 1. Project Overview

**Teekit** (`teekit.co.uk`) is a UK-based e-commerce/marketplace platform connecting sellers (shopkeepers) with buyers, providing product ordering, delivery logistics (Stuart, Gophr, Uber), and a van-based operative/inventory system.

| Layer | Technology |
|---|---|
| Framework | Laravel 11 (PHP 8.3) |
| Frontend | AdminLTE 3 + Bootstrap 5 + Livewire 3 + jQuery |
| Database | MySQL |
| Search | MeiliSearch (Laravel Scout) |
| Payments | Stripe (Laravel Cashier) |
| Auth | JWT (tymon/jwt-auth) for APIs, Laravel session auth for web |
| SMS/WhatsApp | Twilio |
| Storage | DigitalOcean Spaces (S3-compatible) |
| Email | Mailgun (Symfony mailer) |
| Build | Vite 6 + laravel-vite-plugin |
| Docker | PHP 8.3-FPM + Nginx + MySQL |

---

## 2. Architecture & Design Patterns

### 2.1 Directory Structure

```
app/
├── Actions/           # Single-responsibility action classes (business logic)
├── Console/Commands/  # Custom artisan commands (make:action, make:service)
├── Enums/             # PHP 8.1 backed enums
├── Exports/           # Excel exports (maatwebsite/excel)
├── Http/
│   ├── Controllers/
│   │   ├── Api/v1/    # Legacy API controllers (DO NOT follow this pattern)
│   │   ├── Api/v2/    # Modern API controllers (follow VanController pattern)
│   │   └── Web/v1/    # Web controllers
│   ├── Middleware/     # JWT, TransactionWrapper, role-based guards
│   └── Requests/      # Form Request validation classes
├── Imports/           # Excel/CSV imports
├── Jobs/              # Queue jobs
├── Livewire/          # Livewire components
├── Mail/              # Mailable classes
├── Models/            # Eloquent models (implement ModelsInterface contract)
├── Notifications/     # Notification classes
├── Policies/          # Authorization policies
├── Providers/         # Service providers
├── Rules/             # Custom validation rules
├── Services/          # Reusable service classes (static, final)
└── View/Components/   # Blade view components
```

### 2.2 The Action Pattern (MANDATORY for all new code)

All business logic MUST live in **Action classes** under `app/Actions/`. Controllers must remain thin.

**Scaffolding:** Use the custom artisan command:
```bash
php artisan make:action {Name}
```

**Action class rules:**
- Always declare as `final class`
- Must have a single public method named `execute()`
- Use typed parameters and return types
- Keep the action focused — one action per use case
- Actions can be reused across API controllers, Web controllers, and Livewire components

**Reference Action:**
```php
use App\Models\Van;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListVanAction
{
    public function execute(array $filters, array $columns = ['*']): Van|LengthAwarePaginator
    {
        if (isset($filters['id'])) {
            return Van::getById($filters['id'], $columns);
        }

        return Van::getAll($filters['orderBy'], $filters['search'], $columns);
    }
}
```

### 2.3 Controller Pattern (MANDATORY — inspired by VanController)

Every new controller — API or Web — MUST follow this exact 3-step pattern per method:

1. **Request validation** — via a dedicated `FormRequest` class (injected as method parameter)
2. **Action class execution** — business logic delegated to an Action (injected as method parameter)
3. **Return the final response** — using the appropriate response service

#### API Controller Reference (copy this pattern exactly):

```php
<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\Van\LoginVanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Van\LoginVanRequest;
use App\Services\JsonResponseServices;
use Illuminate\Http\JsonResponse;

class VanController extends Controller
{
    public function loginVan(LoginVanRequest $request, LoginVanAction $loginVanAction): JsonResponse
    {
        // Step 1: Validation (handled automatically by FormRequest)
        $validatedData = (object) $request->validated();

        // Step 2: Action execution
        $data = $loginVanAction->execute($validatedData->userName, $validatedData->password);

        // Step 3: Return JSON response
        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            'You have logged in successfully',
            config('constants.HTTP_OK')
        );
    }
}
```

#### Web Controller Reference:

```php
public function store(StoreItemRequest $request, CreateItemAction $action)
{
    // Step 1: Validation (handled automatically by FormRequest)
    $validatedData = (object) $request->validated();

    // Step 2: Action execution
    $action->execute($validatedData);

    // Step 3: Return web response (redirect with flash message)
    WebResponseServices::getWebResponse(true, config('constants.SUCCESS_MESSAGE'));
    return redirect()->back();
}
```

#### Rules for all controllers:
- **NEVER** write business logic inside controllers — delegate to Actions
- **NEVER** use `Validator::make()` inline — always use FormRequest classes
- **ALWAYS** inject Actions and FormRequests via **method-level dependency injection** (not constructor)
- **ALWAYS** cast validated data: `$validatedData = (object) $request->validated();`
- **ALWAYS** use return type hints (`: JsonResponse` for APIs)
- API responses MUST go through `JsonResponseServices::getApiResponse()`
- Web responses MUST use `WebResponseServices` methods or `redirect()->back()`
- Use constants from `config('constants.*')` for status flags, messages, and HTTP codes
- **DO NOT** follow the v1 API controller pattern (fat controllers with inline logic) — that is legacy

### 2.4 Form Request Classes

- Place under `app/Http/Requests/{Module}/`
- Naming convention: `{Verb}{Noun}Request` (e.g., `LoginVanRequest`, `StoreVanInventoryRequest`)
- Always implement `authorize()` — use Policies when applicable
- Use `prepareForValidation()` to merge route parameters when needed
- Authorization can inject Policies via constructor

```php
class ListVanRequest extends FormRequest
{
    public function __construct(public VanPolicy $vanPolicy) {}

    public function authorize(): bool
    {
        return $this->vanPolicy->view(
            user: null,
            van: Van::findOrFail($this->route('vanId'))
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('vanId')]);
    }

    public function rules(): array
    {
        return ['id' => 'required|integer|exists:vans,id'];
    }
}
```

### 2.5 Response Services

**API responses** — Always use `JsonResponseServices` (final, static methods):
| Method | Use Case |
|---|---|
| `getApiResponse($data, $status, $message, $httpCode)` | Standard response with envelope `{data, status, message}` |
| `getApiValidationFailedResponse($errors)` | Validation failures (422) |
| `getPaginatedApiResponse($data, ...)` | Paginated lists (adds `pagination` key) |
| `getApiResponseExtention(...)` | Response with an extra custom key |

**Web responses** — Always use `WebResponseServices`:
| Method | Use Case |
|---|---|
| `getWebResponse($status, $message)` | Flash success/error message |
| `getValidationResponseRedirectBack($errors)` | Redirect back with validation errors |
| `getResponseRedirectBack($status, $message)` | Redirect back with status |

### 2.6 Service Classes

- Place under `app/Services/`
- Naming convention: `{Noun}Services` (e.g., `StripeServices`, `TwilioSmsServices`)
- Declare as `final class` with `static` methods
- Use for cross-cutting concerns: payments, SMS, image processing, external APIs
- Scaffold with: `php artisan make:service {Name}`

### 2.7 Model Conventions

- All models SHOULD implement `App\Models\Contract\ModelsInterface` which defines: `add()`, `addOrUpdate()`, `getById()`, `getAll()`, `updateInfo()`, `deleteTemporarily()`, `deletePermanently()`
- Use `SoftDeletes` trait unless there's a specific reason not to
- Authenticatable models (User, Van, Driver) implement `JWTSubject`
- Models can define `getValidationRules()` for use by Livewire components
- Polymorphic relations MUST be registered in the morph map (`EloquentRelationServiceProvider`)

### 2.8 Enum Conventions

- Use PHP 8.1 backed enums under `app/Enums/`
- Naming convention: `{Noun}Enum` (e.g., `OrderStatusEnum`, `DeliveryProviderEnum`)
- Prefer `string` backed enums for human-readable values, `int` for flags/roles
- Always use enums instead of magic strings or numbers in business logic

---

## 3. Authentication & Authorization

### 3.1 Guards

| Guard | Driver | Model | Usage |
|---|---|---|---|
| `api` | jwt | User | Standard API auth |
| `rider` | jwt | Driver | Driver API auth |
| `van` | jwt | Van | Van operative API auth |
| `van_inventory` | jwt | VanInventory | Van inventory API auth |

### 3.2 Middleware

| Middleware | Alias | Purpose |
|---|---|---|
| `JwtMiddleware` | `jwt.verify` | JWT auth (supports guard param: `jwt.verify:van`) |
| `TransactionWrapper` | `transaction.wrapper` | Wraps requests in DB transaction |
| `AuthenticateSuperAdmin` | `auth.super.admin` | SuperAdmin-only access |
| `AuthenticateParentChildSeller` | `auth.sellers` | Seller/ChildSeller access |

### 3.3 Authorization

- Use **Policies** for model-level authorization
- Place under `app/Policies/`
- Inject Policies into FormRequest `authorize()` methods or use `$this->authorize()` in Livewire
- Role checks use `UserRoleEnum` values

---

## 4. Routing Conventions

### 4.1 API Routes (`routes/api.php`)

- All API routes MUST be wrapped in `transaction.wrapper` middleware
- Group by resource under a prefix: `van/`, `order/`, `product/`
- Protected routes use `jwt.verify` middleware (with optional guard: `jwt.verify:van`)
- API versioning: new routes go under `Api/v2/` controllers
- Always use `Route::prefix()->middleware()->group()` pattern

### 4.2 Web Routes (`routes/web.php`)

- All web routes MUST be wrapped in `transaction.wrapper` middleware
- Admin routes: `admin/` prefix with `auth` + `auth.super.admin` middleware
- Seller routes: `seller/` prefix with `auth` + `auth.sellers` middleware
- Use named routes for Livewire and blade template references

---

## 5. Frontend Guidelines (STRICTLY ENFORCED)

### 5.1 CSS Framework — Bootstrap 5 ONLY

> **DO NOT write custom CSS.** Use Bootstrap 5 utility classes for all styling and responsiveness.

- This project uses **Bootstrap 5** classes for layout, spacing, typography, and components
- AdminLTE 3 provides the dashboard layout shell (sidebar, navbar, content-wrapper)
- For any new component or view, rely entirely on Bootstrap 5 utility classes
- Only use the existing project custom classes listed below — do not create new CSS

### 5.2 Color Scheme (MUST follow exactly)

The project uses a consistent navy-blue and golden-yellow brand palette. All new components MUST adhere to this color scheme.

#### CSS Variables (defined in `public/css/app.css`)

| Variable | Value | Usage |
|---|---|---|
| `--primary-bg` | `#3a4b83` | Primary brand color — navy/indigo blue |
| `--primary-bg-hover` | `#29365f` | Hover state for primary elements |
| `--primary-yellow` | `#ffcf42` | Accent/CTA color — golden yellow |

#### Project Custom Utility Classes (use these, don't invent new ones)

| Class | What It Does |
|---|---|
| `.text-site-primary` | Text in primary navy blue `var(--primary-bg)` |
| `.btn-site-primary` | Navy blue button with white text |
| `.site-primary-bg` | Navy blue background |
| `.site-primary-yellow-bg` | Golden yellow background |

#### Full Color Reference

| Element | Color | Notes |
|---|---|---|
| Primary Blue | `#3a4b83` | Sidebar, buttons, headings, links |
| Primary Blue Hover | `#29365f` | Button/link hover states |
| Accent Yellow | `#ffcf42` | CTA buttons (Add, Update, Import), sidebar active items |
| Body Text | `#444444` | Default paragraph text |
| Form Input Text | `#8aa7d7` | Soft blue for inputs |
| Form Border | `#4a7ed6` | Blue bottom-border on inputs |
| Edit Green | `#26BD75` | Edit action indicators |
| Delete Red | `#fe302f` | Delete actions, error states |
| Background Gray | `#f4f6f9` | Container/card backgrounds |
| Star/Rating Orange | `orange` | Star rating indicators |

### 5.3 Typography

- Font: **Poppins** (Google Fonts), `font-weight: 300` default
- Do not introduce additional fonts

### 5.4 Icons

- Use **Font Awesome 5** (`fas fa-*`, `far fa-*`) exclusively
- Do not add new icon libraries

### 5.5 Component Patterns

#### Buttons
```html
<!-- Primary action (Add, Save, Update) -->
<button class="btn btn-site-primary rounded-pill px-5 py-2">Save</button>

<!-- CTA/Accent (Import, Export) -->
<button class="btn site-primary-yellow-bg rounded-pill px-4">Import</button>

<!-- Danger -->
<button class="btn btn-danger rounded-pill px-4">Delete</button>

<!-- Loading state (Livewire) -->
<button wire:loading.class="btn-dark" wire:loading.attr="disabled" wire:target="action">
    <span wire:loading wire:target="action" class="spinner-border spinner-border-sm"></span>
    Save
</button>
```

#### Modals (Bootstrap 5)
```html
<div class="modal fade" id="modalName" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-site-primary fw-bold">Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form wire:submit="methodName">
                <div class="modal-body">
                    <!-- Form content using Bootstrap grid -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-site-primary rounded-pill px-5 py-2">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

#### Tables
```html
<div class="table-responsive">
    <table class="table table-hover border-bottom">
        <thead class="bg-primary text-white">
            <tr>
                <th>Column</th>
            </tr>
        </thead>
        <tbody>
            <!-- rows -->
        </tbody>
    </table>
</div>
```

#### Dashboard Stat Boxes (AdminLTE)
```html
<div class="col-lg-3 col-6">
    <div class="small-box bg-info">
        <div class="inner">
            <h3>{{ $count }}</h3>
            <p>Label</p>
        </div>
        <div class="icon"><i class="fas fa-icon"></i></div>
    </div>
</div>
```

#### Form Inputs
```html
<div class="mb-3">
    <label class="form-label text-site-primary fw-semibold">Field Name</label>
    <input type="text" class="form-control" wire:model="property">
    <small class="text-danger">@error('property') {{ $message }} @enderror</small>
</div>
```

#### Search Bar
```html
<div class="input-group mb-3">
    <input type="search" class="form-control" placeholder="Search..."
           wire:model.live.debounce.300ms="search">
    <span class="input-group-text"><i class="fas fa-search"></i></span>
</div>
```

### 5.6 Responsiveness

- Use Bootstrap 5 grid system: `col-sm-*`, `col-md-*`, `col-lg-*`, `col-xl-*`
- Use responsive utilities: `d-none d-md-block`, `table-responsive`, etc.
- Test components at all breakpoints
- Do NOT write custom media queries — use Bootstrap breakpoint classes

---

## 6. Livewire Component Guidelines

### 6.1 Structure

- Place under `app/Livewire/{Admin|Common|Sellers}/`
- Naming convention: `{Noun}Livewire` (e.g., `VansLivewire`, `OrdersLivewire`)
- Blade views in `resources/views/livewire/{admin|common|sellers}/`
- View naming: `{noun}-livewire.blade.php`

### 6.2 Patterns

```php
class ItemsLivewire extends Component
{
    use WithPagination, WithFileUploads; // as needed

    // Public properties for form binding
    public $itemId, $name, $search = '';

    // Validation rules (delegate to Model when possible)
    protected function rules(): array
    {
        return (new Item)->getValidationRules($this->itemId);
    }

    // Reset form state
    public function resetComponent(): void
    {
        $this->resetValidation();
        $this->reset(['itemId', 'name']);
    }

    // CRUD methods — authorize → validate → try/catch → flash → dispatch close-modal
    public function addItem(): void
    {
        $this->authorize('create', Item::class);
        $this->validate();

        try {
            // Use Action or Model static method
            (new CreateItemAction)->execute([...]);
            session()->flash('success', config('constants.SUCCESS_MESSAGE'));
            $this->dispatch('close-modal', ['id' => 'addItemModal']);
            $this->resetComponent();
        } catch (\Exception $e) {
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    // Render — reuse Action classes from API/Web controllers
    public function render(): View
    {
        $this->authorize('viewAny', Item::class);
        $data = (new ListItemAction)->execute([
            'orderBy' => OrderByEnum::DESC,
            'search'  => $this->search,
        ]);
        return view('livewire.admin.items-livewire', compact('data'));
    }
}
```

### 6.3 Key Rules
- Reuse the same Action classes across Livewire, API, and Web controllers
- Use `$this->authorize()` with Policies before every state-changing action
- Flash messages via `session()->flash('success'|'error', config('constants.*'))`
- Close modals via `$this->dispatch('close-modal', ['id' => 'modalId'])`
- Bind search with `wire:model.live.debounce.300ms="search"`

---

## 7. Database & Migration Conventions

- Migration naming: `create_{table}_table` or `add_{column}_to_{table}_table`
- Always define `down()` for rollback
- Use `SoftDeletes` (add `deleted_at` column) by default
- Use foreign key constraints with `constrained()->cascadeOnDelete()` where appropriate
- Register any new polymorphic types in `EloquentRelationServiceProvider`

---

## 8. Naming Conventions Summary

| Element | Convention | Example |
|---|---|---|
| Action class | `{Verb}{Noun}Action` (final) | `CreateVanInventoryAction` |
| Action method | Always `execute()` | `$action->execute(...)` |
| Form Request | `{Verb}{Noun}Request` | `StoreVanInventoryRequest` |
| Service class | `{Noun}Services` (final, static) | `JsonResponseServices` |
| Livewire class | `{Noun}Livewire` | `VansLivewire` |
| Livewire view | `{noun}-livewire.blade.php` | `vans-livewire.blade.php` |
| Enum | `{Noun}Enum` (backed) | `OrderStatusEnum` |
| Policy | `{Model}Policy` | `VanPolicy` |
| API Controller | `Api/v2/{Noun}Controller` | `Api/v2/VanController` |
| Web Controller | `Web/v1/{Noun}Controller` | `Web/v1/VanController` |
| Mailable | `{Descriptive}Mail` | `VanInventoryOrderMail` |
| Job | `{Descriptive}Job` | `MoveOrderToOtherNearBySellersJob` |
| Export | `{Noun}Export` | `VansExport` |
| Import | `{Noun}Import` | `VansImport` |

---

## 9. Testing

- Place tests under `tests/Feature/` and `tests/Unit/`
- Use factories for test data generation (factories exist for most models)
- Use `RefreshDatabase` trait for database tests

---

## 10. Configuration & Constants

- Application constants live in `config/constants.php`
- Access via `config('constants.KEY_NAME')`
- Always use config constants for: status flags (`TRUE_STATUS`, `FALSE_STATUS`), HTTP codes (`HTTP_OK`, `HTTP_SERVER_ERROR`), and user-facing messages
- Third-party API configs: `config/stripe.php`, `config/stuart.php`, `config/gophr.php`, `config/uber.php`, `config/twilio.php`, `config/google.php`
- Do NOT hardcode status codes, messages, or config values — always reference `config('constants.*')`

---

## 11. Quick Checklist for New Modules

Before submitting any new module, verify:

- [ ] **Controller** follows the 3-step pattern (FormRequest → Action → Response)
- [ ] **No business logic** inside controllers
- [ ] **Action class** is `final`, has `execute()` method, with typed params/returns
- [ ] **FormRequest** class handles validation and authorization
- [ ] **API responses** use `JsonResponseServices::getApiResponse()`
- [ ] **Web responses** use `WebResponseServices` methods
- [ ] **Constants** referenced from `config('constants.*')` — no hardcoded strings
- [ ] **Enums** used instead of magic strings/numbers
- [ ] **Policies** enforce authorization
- [ ] **Routes** wrapped in `transaction.wrapper` middleware
- [ ] **Frontend** uses Bootstrap 5 classes only — no custom CSS
- [ ] **Color scheme** matches project palette (navy `#3a4b83`, yellow `#ffcf42`)
- [ ] **Responsive** using Bootstrap grid and responsive utilities
- [ ] **Icons** use Font Awesome 5 only
- [ ] **Livewire** components follow the `{Noun}Livewire` naming pattern
- [ ] **Models** do not implement `ModelsInterface` but take inspiration from the functions defined in it, when developing CRUD based functions and use `SoftDeletes`
- [ ] **Migrations** include `down()` method and `softDeletes()`
