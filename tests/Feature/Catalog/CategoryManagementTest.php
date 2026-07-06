<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Admin\Catalog\Categories\CreateForm;
use App\Livewire\Admin\Catalog\Categories\EditForm;
use App\Livewire\Admin\Catalog\Categories\Index;
use App\Models\Catalog\Category;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    /**
     * Category model does not use HasFactory (unlike Product, Customer, Lead, Quote...),
     * so `Category::factory()` is unavailable. Use the factory class directly.
     */
    protected function makeCategory(array $attrs = []): Category
    {
        return CategoryFactory::new()->create($attrs);
    }

    // ── viewAny (Index) works correctly for internal roles ──────────────────

    public function test_catalog_manager_can_see_categories_index(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('catalog-manager');
        $this->makeCategory(['name_ru' => 'Весы электронные']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertOk()
            ->assertSee('Весы электронные');
    }

    public function test_sales_manager_can_see_categories_index(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('sales-manager');
        $this->makeCategory(['name_ru' => 'Принтеры чеков']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertOk()
            ->assertSee('Принтеры чеков');
    }

    // ── BUG: CategoryPolicy has no create()/update()/delete() methods ───────

    /**
     * BUG: App\Policies\CategoryPolicy only implements viewAny()/view(). It has
     * no create(), update() or delete() method at all. There is no Gate::before
     * callback anywhere in the app (grepped app/Providers — none registered), so
     * when App\Livewire\Admin\Catalog\Categories\CreateForm::save() calls
     * `$this->authorize('create', Category::class)`, Laravel's Gate::callPolicyMethod()
     * finds the policy class registered for Category (see AuthServiceProvider::$policies)
     * but sees the 'create' ability is not `is_callable` on it, so it returns false
     * (denied) — for EVERY user, including super-admin. The same happens for
     * 'update' via CategoryPolicy on EditForm::save().
     *
     * Net effect: nobody, not even super-admin or catalog-manager (the roles that
     * legitimately should be able to manage the catalog taxonomy — see
     * ProductPolicy::create()/update() for the correct pattern used for Product),
     * can create or edit a Category through the CRM UI. This silently breaks
     * catalog taxonomy management entirely.
     *
     * This test encodes the SECURE/expected behavior (super-admin should be able
     * to create a category) and currently fails because the real response is 403.
     *
     * Fix belongs to laravel-fullstack: add `create(User $user)`, `update(User $user, Category $category)`,
     * and `delete(User $user, Category $category)` methods to CategoryPolicy,
     * mirroring ProductPolicy's role/permission checks
     * (hasAnyRole(['super-admin','catalog-manager']) || can('catalog.products.*')).
     *
     * NOTE: because `authorize()` runs before `validate()` in both CreateForm::save()
     * and EditForm::save(), this bug also makes it impossible to exercise
     * required-field / unique-slug validation through the real Livewire component
     * until it is fixed — that validation logic is effectively dead code right now.
     */
    public function test_super_admin_can_create_category(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Livewire::actingAs($admin)
            ->test(CreateForm::class)
            ->set('name_ru', 'POS-терминалы')
            ->set('slug', 'pos-terminaly')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'pos-terminaly']);
    }

    /** @see test_super_admin_can_create_category — same root cause, catalog-manager side. */
    public function test_catalog_manager_can_create_category(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');

        Livewire::actingAs($cm)
            ->test(CreateForm::class)
            ->set('name_ru', 'Сканеры штрих-кодов')
            ->set('slug', 'skanery-shtrih-kodov')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'skanery-shtrih-kodov']);
    }

    /**
     * @see test_super_admin_can_create_category — same root cause, EditForm side.
     *
     * EditForm::mount() itself calls `$this->authorize('update', $this->category)`
     * (correctly, mirroring Products\EditForm), so the 403 happens as soon as the
     * component mounts — before we even reach `->set()`/`->call('save')`.
     */
    public function test_catalog_manager_can_update_category(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $category = $this->makeCategory(['name_ru' => 'Старое имя']);

        Livewire::actingAs($cm)
            ->test(EditForm::class, ['categoryId' => $category->id])
            ->assertOk();
    }

    // ── Deny scenarios (pass today — every role is denied by the bug above,
    //    but these also encode the intended access boundary: sales-manager
    //    should never be able to manage catalog taxonomy). ───────────────────

    public function test_sales_manager_cannot_create_category(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');

        Livewire::actingAs($sm)
            ->test(CreateForm::class)
            ->set('name_ru', 'Не должно создаться')
            ->set('slug', 'ne-dolzhno-sozdatsya')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['slug' => 'ne-dolzhno-sozdatsya']);
    }

    public function test_sales_manager_cannot_update_category(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $category = $this->makeCategory(['name_ru' => 'Оригинал']);

        // 403 happens at mount() time (authorize('update', ...)) before any set()/save().
        Livewire::actingAs($sm)
            ->test(EditForm::class, ['categoryId' => $category->id])
            ->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name_ru' => 'Оригинал']);
    }
}
