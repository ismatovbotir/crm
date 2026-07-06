<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Admin\Catalog\Products\CreateForm;
use App\Livewire\Admin\Catalog\Products\EditForm;
use App\Livewire\Admin\Catalog\Products\Index;
use App\Models\Catalog\Product;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    // ── viewAny (Index) ───────────────────────────────────────────────────

    public function test_catalog_manager_can_see_products_index(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('catalog-manager');
        ProductFactory::new()->create(['name_ru' => 'Весы CAS SW-1']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertOk()
            ->assertSee('Весы CAS SW-1');
    }

    public function test_sales_manager_can_see_products_index(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('sales-manager');
        ProductFactory::new()->create(['name_ru' => 'Сканер Honeywell 1900']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertOk()
            ->assertSee('Сканер Honeywell 1900');
    }

    // ── create: allow (super-admin / catalog-manager) ────────────────────

    public function test_catalog_manager_can_create_product(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $category = CategoryFactory::new()->create();

        Livewire::actingAs($cm)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-1001')
            ->set('name_ru', 'POS-терминал Sunmi T2')
            ->set('category_id', $category->id)
            ->set('retail_price', '5000000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-1001', 'name_ru' => 'POS-терминал Sunmi T2']);
        $product = Product::where('sku', 'SKU-1001')->first();
        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'type'       => 'retail',
            'amount'     => 5000000,
        ]);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'warehouse' => 'main']);
    }

    public function test_super_admin_can_create_product(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $category = CategoryFactory::new()->create();

        Livewire::actingAs($admin)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-2002')
            ->set('name_ru', 'Принтер этикеток Zebra')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-2002']);
    }

    // ── create: deny (sales-manager, tech-support, accountant) ───────────

    public function test_sales_manager_cannot_create_product(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $category = CategoryFactory::new()->create();

        Livewire::actingAs($sm)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-3003')
            ->set('name_ru', 'Не должно создаться')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-3003']);
    }

    public function test_accountant_cannot_create_product(): void
    {
        $this->seedRoles();
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');
        $category = CategoryFactory::new()->create();

        Livewire::actingAs($accountant)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-4004')
            ->set('name_ru', 'Не должно создаться')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-4004']);
    }

    // ── required fields / unique sku validation ───────────────────────────

    public function test_product_requires_name_ru(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $category = CategoryFactory::new()->create();

        Livewire::actingAs($cm)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-5005')
            ->set('name_ru', '')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertHasErrors(['name_ru' => 'required']);

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-5005']);
    }

    public function test_product_requires_valid_category(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');

        Livewire::actingAs($cm)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-6006')
            ->set('name_ru', 'Товар без категории')
            ->set('category_id', null)
            ->call('save')
            ->assertHasErrors(['category_id' => 'required']);

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-6006']);
    }

    public function test_product_sku_must_be_unique(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $category = CategoryFactory::new()->create();
        ProductFactory::new()->create(['sku' => 'SKU-DUPLICATE']);

        Livewire::actingAs($cm)
            ->test(CreateForm::class)
            ->set('sku', 'SKU-DUPLICATE')
            ->set('name_ru', 'Товар-дубликат')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);

        $this->assertDatabaseCount('products', 1);
    }

    // ── update: allow / deny ──────────────────────────────────────────────

    public function test_catalog_manager_can_update_product(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        // name_uz explicitly set to '' (not null) to sidestep the crash documented
        // in test_editing_product_with_null_optional_fields_crashes_editform below —
        // this test is about the update flow itself, not that bug.
        $product = ProductFactory::new()->create(['name_ru' => 'Старое название', 'sku' => 'SKU-7007', 'name_uz' => '']);

        Livewire::actingAs($cm)
            ->test(EditForm::class, ['productId' => $product->id])
            ->set('name_ru', 'Новое название')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_ru' => 'Новое название']);
    }

    public function test_sales_manager_cannot_update_product(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $product = ProductFactory::new()->create(['name_ru' => 'Оригинал']);

        // ProductPolicy::update() denies non catalog roles; EditForm::mount()
        // authorizes before rendering, so the 403 happens right at mount().
        Livewire::actingAs($sm)
            ->test(EditForm::class, ['productId' => $product->id])
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_ru' => 'Оригинал']);
    }

    public function test_updating_product_sku_to_another_products_sku_fails_validation(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        ProductFactory::new()->create(['sku' => 'SKU-TAKEN']);
        // name_uz explicitly '' to sidestep the EditForm::mount() crash on null
        // optional fields — see test_editing_product_with_null_optional_fields_crashes_editform.
        $product = ProductFactory::new()->create(['sku' => 'SKU-FREE', 'name_uz' => '']);

        Livewire::actingAs($cm)
            ->test(EditForm::class, ['productId' => $product->id])
            ->set('sku', 'SKU-TAKEN')
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => 'SKU-FREE']);
    }

    /**
     * BUG: App\Livewire\Admin\Catalog\Products\EditForm declares its optional
     * text fields as non-nullable typed properties (`public string $name_uz = '';`,
     * and likewise `brand`, `model_number`, `description_ru`), but the
     * corresponding `products` columns are nullable (see
     * database/migrations/2026_04_28_140100_create_products_table.php) and
     * ProductFactory/real seed data legitimately leave them `null` (e.g. a
     * product without an Uzbek translation).
     *
     * EditForm::mount() does:
     *   $this->fill($this->product->only(['sku','name_ru','name_uz','brand',...]));
     * `only()` returns the raw (possibly null) column value, and Livewire's
     * `fill()` performs a direct property assignment — which throws
     * `TypeError: Cannot assign null to property ...::$name_uz of type string`
     * for ANY product whose `name_uz` (or `brand`/`model_number`/`description_ru`)
     * is null. Since these are all optional/nullable fields, this will crash the
     * "Edit product" screen for a large share of real catalog data.
     *
     * This test encodes the expected behavior (edit screen opens fine for a
     * product with a null optional field) and currently errors with the
     * TypeError above instead of rendering.
     *
     * Fix belongs to laravel-fullstack: either coalesce nulls to '' before
     * `fill()` (e.g. `$this->product->only([...])` mapped through `?? ''`),
     * or make the properties nullable (`public ?string $name_uz = null;`) and
     * adjust `save()`/validation accordingly.
     */
    public function test_editing_product_with_null_optional_fields_crashes_editform(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $product = ProductFactory::new()->create([
            'name_uz'        => null,
            'brand'          => null,
            'model_number'   => null,
            'description_ru' => null,
        ]);

        Livewire::actingAs($cm)
            ->test(EditForm::class, ['productId' => $product->id])
            ->assertOk();
    }

    // ── delete: Policy-level allow/deny (no delete action is wired in the UI
    //    yet — Products/Index and Products/Show have no delete button/method —
    //    so this is tested directly against the Gate rather than through a
    //    Livewire component call). ──────────────────────────────────────────

    public function test_catalog_manager_can_delete_product_per_policy(): void
    {
        $this->seedRoles();
        $cm = User::factory()->create();
        $cm->assignRole('catalog-manager');
        $product = ProductFactory::new()->create();

        $this->assertTrue($cm->can('delete', $product));
    }

    public function test_sales_manager_cannot_delete_product_per_policy(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $product = ProductFactory::new()->create();

        $this->assertFalse($sm->can('delete', $product));
    }
}
