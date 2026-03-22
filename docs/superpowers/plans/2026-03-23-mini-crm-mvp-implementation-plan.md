# Mini CRM MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 2-15 kişilik ekipler için self-hosted, Türkçe, hızlı ve premium hissiyatlı bir mini CRM MVP’sini (Günüm + temel CRM modülleri + tam rol/izin) üretime hazır hale getirmek.

**Architecture:** Laravel içinde modüler domain yaklaşımı kullanılacak. Her modül (Contacts, Opportunities, Tasks, Deals, Reporting, Access Control) kendi model/request/controller/test setiyle ilerleyecek ve “Günüm” ekranı bu modüllerden beslenen merkezi aksiyon listesi olarak çalışacak. Önce veri modeli ve yetkilendirme, sonra iş akışları, en sonda dashboard/kurulum/doğrulama tamamlanacak.

**Tech Stack:** Laravel (Blade, Eloquent, Policies, Form Requests), SQLite/MySQL uyumlu migration yapısı, PHP Unit/Feature Tests, minimal vanilla JS + CSS.

---

## Scope Check

Spec modülleri ayrı ürünler değil, tek CRM akışının parçalarıdır. Bu yüzden tek plan içinde fazlara bölünmüştür; her faz çalışır yazılım üretir.

## Spec -> Task Traceability

- Türkçe varsayılan dil + yönlendirici hata mesajları -> Task 1
- Müşteri/şirket kartları -> Task 4
- Fırsat/pipeline yönetimi -> Task 5
- Görev ve hatırlatıcılar -> Task 6
- Teklif/satış kaydı -> Task 7
- Tam özelleştirilebilir rol/izin matrisi -> Task 3 + Task 3B
- Takım yönetimi (Yönetim menüsü kapsamı) -> Task 3C
- Günüm ekranı (öncelik sırası) -> Task 8
- Basit raporlama paneli -> Task 9
- Audit log (özellikle rol/izin + satış) -> Task 10
- Self-hosted kurulum + demo veri -> Task 11
- MVP başarı kriteri regresyonları -> Task 12

## Execution Rules

- TDD disiplini: önce test, sonra minimum implementasyon, sonra refactor.
- YAGNI: Spec dışında özellik ekleme yok.
- Sık commit: Her task sonunda tek amaçlı commit.
- Doğrulama: Her task sonunda ilgili hedef testi çalıştır.
- Yardımcı skill referansı: `@superpowers/test-driven-development`, `@superpowers/verification-before-completion`.

## File Structure (Planned)

### Domain Models
- Create: `app/Models/Company.php` — şirket kaydı
- Create: `app/Models/Contact.php` — kişi kaydı, company ilişkisi
- Create: `app/Models/Opportunity.php` — pipeline fırsatı
- Create: `app/Models/OpportunityStage.php` — özelleştirilebilir aşamalar
- Create: `app/Models/CrmTask.php` — görev/hatırlatıcı (Task model adı çakışmaması için CrmTask)
- Create: `app/Models/Deal.php` — teklif/satış kaydı
- Create: `app/Models/Role.php` — rol tanımı
- Create: `app/Models/Permission.php` — izin tanımı
- Create: `app/Models/AuditLog.php` — kritik işlem kayıtları

### HTTP Layer
- Create: `app/Http/Controllers/DashboardController.php`
- Create: `app/Http/Controllers/TodayController.php`
- Create: `app/Http/Controllers/CompanyController.php`
- Create: `app/Http/Controllers/ContactController.php`
- Create: `app/Http/Controllers/OpportunityController.php`
- Create: `app/Http/Controllers/CrmTaskController.php`
- Create: `app/Http/Controllers/DealController.php`
- Create: `app/Http/Controllers/RoleController.php`
- Create: `app/Http/Controllers/TeamController.php`
- Create: `app/Http/Requests/*` (module bazlı validation request’leri)

### Services / Policies
- Create: `app/Services/Today/TodayPriorityService.php` — Günüm sıralama motoru
- Create: `app/Services/Permissions/PermissionResolver.php` — rol+izin çözümleme
- Create: `app/Policies/*Policy.php` — kaynak bazlı erişim kuralları

### Database
- Create: `database/migrations/2026_03_23_000100_create_companies_table.php`
- Create: `database/migrations/2026_03_23_000110_create_contacts_table.php`
- Create: `database/migrations/2026_03_23_000120_create_opportunity_stages_table.php`
- Create: `database/migrations/2026_03_23_000130_create_opportunities_table.php`
- Create: `database/migrations/2026_03_23_000140_create_crm_tasks_table.php`
- Create: `database/migrations/2026_03_23_000150_create_deals_table.php`
- Create: `database/migrations/2026_03_23_000160_create_roles_permissions_tables.php`
- Create: `database/migrations/2026_03_23_000170_create_audit_logs_table.php`
- Modify: `database/seeders/DatabaseSeeder.php` — demo seed
- Create: `database/seeders/CrmDemoSeeder.php`

### Views / Routes
- Modify: `routes/web.php`
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/today/index.blade.php`
- Create: `resources/views/dashboard/index.blade.php`
- Create: `resources/views/{companies,contacts,opportunities,tasks,deals,roles,team}/*.blade.php`
- Modify: `resources/css/app.css`

### Tests
- Create: `tests/Feature/Auth/LoginTest.php`
- Create: `tests/Feature/Today/TodayPageTest.php`
- Create: `tests/Feature/Permissions/PermissionMatrixTest.php`
- Create: `tests/Feature/Opportunities/OpportunityLifecycleTest.php`
- Create: `tests/Feature/Deals/DealConversionTest.php`
- Create: `tests/Feature/Reports/DashboardMetricsTest.php`
- Create: `tests/Unit/Today/TodayPriorityServiceTest.php`
- Create: `tests/Unit/Permissions/PermissionResolverTest.php`
- Create: `tests/Feature/Install/SmokeInstallFlowTest.php`
- Create: `tests/Feature/Team/TeamManagementTest.php`

## Task Plan

### Task 1: Authentication Shell + App Layout

**Files:**
- Create: `tests/Feature/Auth/LoginTest.php`
- Modify: `routes/web.php`
- Modify: `config/app.php`
- Create: `resources/lang/tr/validation.php`
- Create: `resources/lang/tr/auth.php`
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `app/Http/Controllers/Auth/LoginController.php`

- [ ] **Step 1: Write the failing test**

```php
public function test_user_can_log_in_and_is_redirected_to_today(): void
{
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertRedirect('/today');
    $this->assertAuthenticatedAs($user);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LoginTest`
Expected: FAIL (`POST /login` route not found)

- [ ] **Step 3: Write minimal implementation**

```php
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::redirect('/', '/today');
```

```php
// config/app.php
'locale' => 'tr',
'fallback_locale' => 'tr',
```

```php
// resources/lang/tr/validation.php
'required' => ':attribute alanı zorunludur.',
'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LoginTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Auth/LoginTest.php routes/web.php config/app.php resources/lang/tr/validation.php resources/lang/tr/auth.php app/Http/Controllers/Auth/LoginController.php resources/views/layouts/app.blade.php resources/views/auth/login.blade.php
git commit -m "feat: add auth shell with turkish locale defaults"
```

### Task 2: CRM Core Schema (Companies, Contacts, Stages, Opportunities, Tasks, Deals)

**Files:**
- Create: all CRM migrations listed in File Structure
- Create: `app/Models/Company.php`
- Create: `app/Models/Contact.php`
- Create: `app/Models/OpportunityStage.php`
- Create: `app/Models/Opportunity.php`
- Create: `app/Models/CrmTask.php`
- Create: `app/Models/Deal.php`
- Create: `database/factories/CompanyFactory.php`
- Create: `database/factories/ContactFactory.php`
- Create: `database/factories/OpportunityStageFactory.php`
- Create: `database/factories/OpportunityFactory.php`
- Create: `database/factories/CrmTaskFactory.php`
- Create: `database/factories/DealFactory.php`
- Create: `tests/Feature/Opportunities/OpportunityLifecycleTest.php`

- [ ] **Step 1: Write the failing lifecycle test**

```php
public function test_opportunity_relates_to_contact_stage_and_tasks(): void
{
    $contact = Contact::factory()->create();
    $stage = OpportunityStage::factory()->create(['name' => 'Yeni']);
    $opportunity = Opportunity::factory()->create([
        'contact_id' => $contact->id,
        'opportunity_stage_id' => $stage->id,
    ]);

    CrmTask::factory()->create(['opportunity_id' => $opportunity->id]);

    $this->assertCount(1, $opportunity->tasks);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OpportunityLifecycleTest`
Expected: FAIL (table/model ilişki eksik)

- [ ] **Step 3: Write minimal implementation**

```php
// Opportunity.php
public function tasks(): HasMany
{
    return $this->hasMany(CrmTask::class);
}
```

- [ ] **Step 4: Run test + migrate**

Run: `php artisan migrate:fresh --seed && php artisan test --filter=OpportunityLifecycleTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations app/Models database/factories tests/Feature/Opportunities/OpportunityLifecycleTest.php
git commit -m "feat: add core crm schema models and factories"
```

### Task 3: Role/Permission Matrix Foundations

**Files:**
- Create: `app/Models/Role.php`
- Create: `app/Models/Permission.php`
- Create: `database/factories/RoleFactory.php`
- Create: `database/factories/PermissionFactory.php`
- Create: `app/Services/Permissions/PermissionResolver.php`
- Create: `app/Policies/CompanyPolicy.php`
- Create: `tests/Unit/Permissions/PermissionResolverTest.php`
- Create: `tests/Feature/Permissions/PermissionMatrixTest.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Write failing permission resolver test**

```php
public function test_user_has_permission_through_role(): void
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['key' => 'companies.view']);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role);

    $resolver = app(PermissionResolver::class);
    $this->assertTrue($resolver->can($user, 'companies.view'));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PermissionResolverTest`
Expected: FAIL (`roles()` veya `can()` eksik)

- [ ] **Step 3: Implement minimal resolver + relations**

```php
public function can(User $user, string $permissionKey): bool
{
    return $user->permissions()
        ->where('key', $permissionKey)
        ->exists();
}
```

- [ ] **Step 4: Verify unit + feature permission checks**

Run: `php artisan test --filter=PermissionResolverTest && php artisan test --filter=PermissionMatrixTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Models/User.php app/Models/Role.php app/Models/Permission.php database/factories/RoleFactory.php database/factories/PermissionFactory.php app/Services/Permissions app/Policies tests/Unit/Permissions tests/Feature/Permissions database/migrations
git commit -m "feat: implement customizable role permission matrix foundation"
```

### Task 3B: Role/Permission Management Workflows

**Files:**
- Create: `app/Http/Controllers/RoleController.php`
- Create: `app/Http/Requests/StoreRoleRequest.php`
- Create: `app/Http/Requests/UpdateRoleRequest.php`
- Create: `resources/views/roles/index.blade.php`
- Create: `resources/views/roles/create.blade.php`
- Create: `resources/views/roles/edit.blade.php`
- Create: `tests/Feature/Permissions/RoleManagementTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing role creation + permission assignment test**

```php
public function test_admin_can_create_role_and_assign_action_permissions(): void
{
    $admin = User::factory()->create();
    $this->actingAs($admin)
        ->post('/roles', [
            'name' => 'Satis',
            'permissions' => [
                'companies.view',
                'companies.create',
                'opportunities.edit',
                'deals.export',
            ],
        ])
        ->assertRedirect('/roles');

    $this->assertDatabaseHas('roles', ['name' => 'Satis']);
    $this->assertDatabaseHas('permissions', ['key' => 'deals.export']);
}
```

```php
public function test_non_admin_cannot_create_roles(): void
{
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/roles', ['name' => 'Yetkisiz'])
        ->assertForbidden();
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RoleManagementTest`
Expected: FAIL (`/roles` route ve persistence akışı yok)

- [ ] **Step 3: Implement role CRUD + module/action permission assignment**

```php
$role = Role::create(['name' => $request->name]);
$permissionIds = Permission::query()
    ->whereIn('key', $request->input('permissions', []))
    ->pluck('id');
$role->permissions()->sync($permissionIds);
```

- [ ] **Step 4: Run role management tests**

Run: `php artisan test --filter=RoleManagementTest && php artisan test --filter=PermissionMatrixTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/RoleController.php app/Http/Requests/StoreRoleRequest.php app/Http/Requests/UpdateRoleRequest.php resources/views/roles routes/web.php tests/Feature/Permissions/RoleManagementTest.php
git commit -m "feat: add role management and action-based permission assignment"
```

### Task 3C: Team Management (Yönetim > Takım)

**Files:**
- Create: `app/Http/Controllers/TeamController.php`
- Create: `app/Http/Requests/StoreTeamMemberRequest.php`
- Create: `resources/views/team/index.blade.php`
- Create: `resources/views/team/create.blade.php`
- Create: `tests/Feature/Team/TeamManagementTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing team member onboarding test**

```php
public function test_admin_can_add_team_member_and_assign_role(): void
{
    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'Operasyon']);

    $this->actingAs($admin)
        ->post('/team', [
            'name' => 'Ece Kaya',
            'email' => 'ece@example.com',
            'password' => 'secret123',
            'role_ids' => [$role->id],
        ])
        ->assertRedirect('/team');

    $this->assertDatabaseHas('users', ['email' => 'ece@example.com']);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TeamManagementTest`
Expected: FAIL (`/team` endpoint yok)

- [ ] **Step 3: Implement minimal team management**

```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => bcrypt($request->password),
]);
$user->roles()->sync($request->role_ids);
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=TeamManagementTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/TeamController.php app/Http/Requests/StoreTeamMemberRequest.php resources/views/team routes/web.php tests/Feature/Team/TeamManagementTest.php
git commit -m "feat: add team management workflow under admin section"
```

### Task 4: Companies + Contacts CRUD

**Files:**
- Create: `app/Http/Controllers/CompanyController.php`
- Create: `app/Http/Controllers/ContactController.php`
- Create: `app/Http/Requests/StoreCompanyRequest.php`
- Create: `app/Http/Requests/StoreContactRequest.php`
- Create: `resources/views/companies/index.blade.php`
- Create: `resources/views/companies/create.blade.php`
- Create: `resources/views/contacts/index.blade.php`
- Create: `resources/views/contacts/create.blade.php`
- Create: `tests/Feature/Contacts/ContactCrudTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing CRUD test**

```php
public function test_authorized_user_can_create_contact_under_company(): void
{
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $this->actingAs($user)
        ->post('/contacts', [
            'company_id' => $company->id,
            'name' => 'Ayse Yilmaz',
            'email' => 'ayse@example.com',
        ])
        ->assertRedirect('/contacts');

    $this->assertDatabaseHas('contacts', ['email' => 'ayse@example.com']);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ContactCrudTest`
Expected: FAIL (`/contacts` route/controller yok)

- [ ] **Step 3: Implement minimal controller + request + blade forms**

```php
public function store(StoreContactRequest $request): RedirectResponse
{
    Contact::create($request->validated());
    return redirect('/contacts');
}
```

```php
// StoreContactRequest.php
public function messages(): array
{
    return [
        'name.required' => 'Kişi adı zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi girin.',
    ];
}
```

- [ ] **Step 4: Run targeted tests**

Run: `php artisan test --filter=ContactCrudTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/CompanyController.php app/Http/Controllers/ContactController.php app/Http/Requests/StoreCompanyRequest.php app/Http/Requests/StoreContactRequest.php resources/views/companies resources/views/contacts routes/web.php tests/Feature/Contacts/ContactCrudTest.php
git commit -m "feat: add companies and contacts CRUD flows"
```

### Task 5: Opportunity Pipeline Management

**Files:**
- Create: `app/Http/Controllers/OpportunityController.php`
- Create: `app/Http/Requests/StoreOpportunityRequest.php`
- Create: `app/Http/Requests/UpdateOpportunityStageRequest.php`
- Create: `resources/views/opportunities/index.blade.php`
- Create: `resources/views/opportunities/create.blade.php`
- Create: `tests/Feature/Opportunities/OpportunityStageTransitionTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing stage transition test**

```php
public function test_user_can_move_opportunity_between_stages(): void
{
    $opportunity = Opportunity::factory()->create();
    $nextStage = OpportunityStage::factory()->create(['name' => 'Teklif']);

    $this->actingAs(User::factory()->create())
        ->patch("/opportunities/{$opportunity->id}/stage", [
            'opportunity_stage_id' => $nextStage->id,
        ])
        ->assertRedirect('/opportunities');

    $this->assertDatabaseHas('opportunities', [
        'id' => $opportunity->id,
        'opportunity_stage_id' => $nextStage->id,
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OpportunityStageTransitionTest`
Expected: FAIL (route/action yok)

- [ ] **Step 3: Implement minimal transition action**

```php
public function updateStage(UpdateOpportunityStageRequest $request, Opportunity $opportunity): RedirectResponse
{
    $opportunity->update($request->validated());
    return redirect('/opportunities');
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=OpportunityStageTransitionTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/OpportunityController.php app/Http/Requests/StoreOpportunityRequest.php app/Http/Requests/UpdateOpportunityStageRequest.php resources/views/opportunities routes/web.php tests/Feature/Opportunities/OpportunityStageTransitionTest.php
git commit -m "feat: add opportunity pipeline and stage transitions"
```

### Task 6: Tasks & Reminder Workflow

**Files:**
- Create: `app/Http/Controllers/CrmTaskController.php`
- Create: `app/Http/Requests/StoreCrmTaskRequest.php`
- Create: `resources/views/tasks/index.blade.php`
- Create: `resources/views/tasks/create.blade.php`
- Create: `tests/Feature/Tasks/TaskReminderTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing overdue task visibility test**

```php
public function test_overdue_tasks_are_listed_on_task_index(): void
{
    CrmTask::factory()->create(['due_at' => now()->subDay(), 'status' => 'open']);

    $this->actingAs(User::factory()->create())
        ->get('/tasks')
        ->assertSee('Gecikmiş');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TaskReminderTest`
Expected: FAIL (`/tasks` ekranı veya etiket yok)

- [ ] **Step 3: Implement minimal index/store flow**

```php
$overdueTasks = CrmTask::query()
    ->where('status', 'open')
    ->where('due_at', '<', now())
    ->get();
```

- [ ] **Step 4: Run test**

Run: `php artisan test --filter=TaskReminderTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/CrmTaskController.php app/Http/Requests/StoreCrmTaskRequest.php resources/views/tasks routes/web.php tests/Feature/Tasks/TaskReminderTest.php
git commit -m "feat: add crm task and overdue reminder workflow"
```

### Task 7: Deal/Quote Conversion Flow

**Files:**
- Create: `app/Http/Controllers/DealController.php`
- Create: `app/Http/Requests/StoreDealRequest.php`
- Create: `app/Http/Requests/ConvertOpportunityToDealRequest.php`
- Create: `resources/views/deals/index.blade.php`
- Create: `resources/views/deals/create.blade.php`
- Create: `tests/Feature/Deals/DealConversionTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing conversion test**

```php
public function test_opportunity_can_be_converted_to_deal(): void
{
    $opportunity = Opportunity::factory()->create(['status' => 'open']);

    $this->actingAs(User::factory()->create())
        ->post("/opportunities/{$opportunity->id}/convert-to-deal", [
            'amount' => 15000,
            'status' => 'offer',
        ])
        ->assertRedirect('/deals');

    $this->assertDatabaseHas('deals', ['opportunity_id' => $opportunity->id]);
}
```

```php
public function test_duplicate_conversion_is_blocked_for_same_opportunity(): void
{
    $opportunity = Opportunity::factory()->create(['status' => 'open']);
    Deal::factory()->create(['opportunity_id' => $opportunity->id]);

    $this->actingAs(User::factory()->create())
        ->post("/opportunities/{$opportunity->id}/convert-to-deal", [
            'amount' => 15000,
            'status' => 'offer',
        ])
        ->assertSessionHasErrors('opportunity_id');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DealConversionTest`
Expected: FAIL (convert endpoint yok)

- [ ] **Step 3: Implement conversion endpoint**

```php
DB::transaction(function () use ($opportunity, $request) {
    $locked = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();

    if (Deal::where('opportunity_id', $locked->id)->exists()) {
        throw ValidationException::withMessages([
            'opportunity_id' => 'Bu fırsat için zaten teklif/satış kaydı var.',
        ]);
    }

    Deal::create([
        'opportunity_id' => $locked->id,
        'amount' => $request->amount,
        'status' => $request->status,
    ]);
});
```

Not: Task 2 kapsamındaki `deals` migration’ında `opportunity_id` için `unique()` kısıtı eklenir.

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=DealConversionTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DealController.php app/Http/Requests/StoreDealRequest.php app/Http/Requests/ConvertOpportunityToDealRequest.php resources/views/deals routes/web.php tests/Feature/Deals/DealConversionTest.php database/migrations
git commit -m "feat: add safe opportunity to deal conversion with collision guards"
```

### Task 8: Today (Günüm) Priority Engine

**Files:**
- Create: `app/Services/Today/TodayPriorityService.php`
- Create: `app/Http/Controllers/TodayController.php`
- Create: `resources/views/today/index.blade.php`
- Create: `tests/Unit/Today/TodayPriorityServiceTest.php`
- Create: `tests/Feature/Today/TodayPageTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing unit test for ordering**

```php
public function test_prioritizes_calls_then_critical_opportunities_then_overdue_tasks(): void
{
    $items = app(TodayPriorityService::class)->buildFor(User::factory()->create());

    $this->assertSame(['call', 'critical_opportunity', 'overdue_task'], array_unique(array_column($items, 'type')));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TodayPriorityServiceTest`
Expected: FAIL (service yok)

- [ ] **Step 3: Implement minimal priority service + page**

```php
usort($items, fn ($a, $b) => $a['priority'] <=> $b['priority']);
```

- [ ] **Step 4: Run unit + feature tests**

Run: `php artisan test --filter=TodayPriorityServiceTest && php artisan test --filter=TodayPageTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/Today app/Http/Controllers/TodayController.php resources/views/today/index.blade.php routes/web.php tests/Unit/Today tests/Feature/Today
git commit -m "feat: add today page priority engine"
```

### Task 9: Dashboard Metrics (Basic Reporting)

**Files:**
- Create: `app/Http/Controllers/DashboardController.php`
- Create: `resources/views/dashboard/index.blade.php`
- Create: `tests/Feature/Reports/DashboardMetricsTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing metrics test**

```php
public function test_dashboard_shows_open_opportunities_and_weekly_closed_deals(): void
{
    Opportunity::factory()->count(3)->create(['status' => 'open']);
    Deal::factory()->count(2)->create(['status' => 'won', 'closed_at' => now()]);

    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertSee('Açık Fırsatlar')
        ->assertSee('3')
        ->assertSee('Haftalık Kapanan Satış')
        ->assertSee('2');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DashboardMetricsTest`
Expected: FAIL (`/dashboard` yok)

- [ ] **Step 3: Implement minimal dashboard query set**

```php
$openOpportunities = Opportunity::where('status', 'open')->count();
$weeklyWonDeals = Deal::where('status', 'won')
    ->whereBetween('closed_at', [now()->startOfWeek(), now()->endOfWeek()])
    ->count();
```

- [ ] **Step 4: Run test**

Run: `php artisan test --filter=DashboardMetricsTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DashboardController.php resources/views/dashboard/index.blade.php routes/web.php tests/Feature/Reports/DashboardMetricsTest.php
git commit -m "feat: add basic crm dashboard metrics"
```

### Task 10: Audit Log For Critical Actions

**Files:**
- Create: `app/Models/AuditLog.php`
- Create: `app/Observers/OpportunityObserver.php`
- Create: `app/Observers/DealObserver.php`
- Create: `app/Observers/RoleObserver.php`
- Create: `app/Observers/PermissionObserver.php`
- Create: `app/Services/Audit/AuditLogger.php`
- Modify: `app/Http/Controllers/RoleController.php`
- Modify: `app/Http/Controllers/DealController.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Audit/AuditLogTest.php`

- [ ] **Step 1: Write failing audit test**

```php
public function test_critical_deal_update_creates_audit_log(): void
{
    $deal = Deal::factory()->create(['status' => 'offer']);

    $this->actingAs(User::factory()->create())
        ->patch("/deals/{$deal->id}", ['status' => 'won']);

    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => Deal::class,
        'entity_id' => $deal->id,
        'action' => 'updated',
    ]);
}
```

```php
public function test_role_permission_change_creates_audit_log(): void
{
    $role = Role::factory()->create(['name' => 'Satis']);
    $permission = Permission::factory()->create(['key' => 'deals.export']);

    $this->actingAs(User::factory()->create())
        ->post("/roles/{$role->id}/permissions", ['permission_ids' => [$permission->id]]);

    $this->assertDatabaseHas('audit_logs', [
        'entity_type' => Role::class,
        'entity_id' => $role->id,
        'action' => 'permissions_synced',
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuditLogTest`
Expected: FAIL (`audit_logs` kaydı oluşmaz)

- [ ] **Step 3: Implement hybrid logging (observers + explicit role-permission sync logging)**

```php
AuditLog::create([
    'user_id' => auth()->id(),
    'entity_type' => Deal::class,
    'entity_id' => $deal->id,
    'action' => 'updated',
    'payload' => ['status' => $deal->status],
]);
```

```php
// RoleController@syncPermissions (explicit log, observer yerine)
$role->permissions()->sync($permissionIds);
$auditLogger->log(
    userId: auth()->id(),
    entityType: Role::class,
    entityId: $role->id,
    action: 'permissions_synced',
    payload: ['permission_ids' => $permissionIds],
);
```

- [ ] **Step 4: Run test**

Run: `php artisan test --filter=AuditLogTest && php artisan test --filter=RoleManagementTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Models/AuditLog.php app/Observers app/Services/Audit/AuditLogger.php app/Http/Controllers/RoleController.php app/Providers/AppServiceProvider.php tests/Feature/Audit/AuditLogTest.php database/migrations
git commit -m "feat: add audit logging for sales and role permission critical actions"
```

### Task 11: Demo Seeder + Install Smoke Test

**Files:**
- Create: `database/seeders/CrmDemoSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `tests/Feature/Install/SmokeInstallFlowTest.php`
- Create: `docs/INSTALL.md`

- [ ] **Step 1: Write failing smoke install test**

```php
public function test_fresh_install_with_seed_shows_today_page_data(): void
{
    $this->artisan('migrate:fresh', ['--seed' => true])->assertSuccessful();
    $user = User::first();

    $this->actingAs($user)
        ->get('/today')
        ->assertOk()
        ->assertSee('Bugün');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SmokeInstallFlowTest`
Expected: FAIL (seeded demo veri/bugün ekranı hazır değil)

- [ ] **Step 3: Implement demo seed + install doc**

```php
// DatabaseSeeder.php
$this->call(CrmDemoSeeder::class);
```

- [ ] **Step 4: Run smoke test**

Run: `php artisan test --filter=SmokeInstallFlowTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/seeders docs/INSTALL.md tests/Feature/Install/SmokeInstallFlowTest.php
git commit -m "chore: add demo seed and installation smoke coverage"
```

### Task 12: Final Regression + Release Readiness

**Files:**
- Modify: `README.md`
- Modify: `docs/INSTALL.md`
- Modify: `resources/css/app.css`
- Modify: `tests/*` (if any stabilization needed)
- Create: `tests/Feature/Acceptance/FirstTenMinutesFlowTest.php`

- [ ] **Step 1: Add failing acceptance test for first 30-second experience**

```php
public function test_today_page_contains_prioritized_action_sections(): void
{
    $this->actingAs(User::factory()->create())
        ->get('/today')
        ->assertSeeInOrder([
            'Aranacak Kişiler',
            'Kritik Fırsatlar',
            'Geciken Görevler',
        ]);
}
```

```php
public function test_new_member_can_create_customer_opportunity_and_task_quickly(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $stage = OpportunityStage::factory()->create(['name' => 'Yeni']);

    $company = Company::create(['name' => 'Demo A.S.']);
    $contact = Contact::create(['company_id' => $company->id, 'name' => 'Mert Can']);
    $opportunity = Opportunity::create([
        'contact_id' => $contact->id,
        'opportunity_stage_id' => $stage->id,
        'title' => 'Yillik Lisans',
    ]);
    CrmTask::create([
        'opportunity_id' => $opportunity->id,
        'title' => 'Ilk aramayi yap',
        'due_at' => now()->addHour(),
    ]);

    $this->assertDatabaseHas('crm_tasks', ['title' => 'Ilk aramayi yap']);
}
```

- [ ] **Step 2: Run targeted test**

Run: `php artisan test --filter=TodayPageTest && php artisan test --filter=FirstTenMinutesFlowTest`
Expected: PASS (or fail then minimal fix until pass)

- [ ] **Step 3: Run full test suite and style checks**

Run: `php artisan test`
Expected: PASS all

Run: `./vendor/bin/pint --test`
Expected: PASS (no formatting issues)

- [ ] **Step 4: Update docs for release**

Run:
```bash
php artisan about
```
Expected: Environment + version info captured for README release notes.

- [ ] **Step 5: Commit**

```bash
git add README.md docs/INSTALL.md resources/css/app.css tests
git commit -m "docs: finalize mvp readiness and verification checklist"
```

## Final Verification Checklist

- [ ] `php artisan migrate:fresh --seed` sorunsuz çalışıyor
- [ ] Yetki testleri geçiyor (`PermissionMatrixTest`)
- [ ] Rol yönetimi ve takım testleri geçiyor (`RoleManagementTest`, `TeamManagementTest`)
- [ ] Günüm sıralama testleri geçiyor (`TodayPriorityServiceTest`, `TodayPageTest`)
- [ ] Fırsat->satış dönüşüm testleri geçiyor (`DealConversionTest`)
- [ ] Dashboard testleri geçiyor (`DashboardMetricsTest`)
- [ ] Tam test paketi yeşil (`php artisan test`)

## Handoff Notes

- Task sırası kritiktir; özellikle Task 2, Task 3, Task 3B ve Task 3C tamamlanmadan UI modüllerine geçilmemeli.
- İhtiyaç halinde parallel çalışma sadece bağımsız dosya kümelerinde yapılmalı (ör. Task 9 ve Task 10, Task 8 sonrası).
- Plan dışı özellik talepleri yeni spec revizyonuna alınmalı.
