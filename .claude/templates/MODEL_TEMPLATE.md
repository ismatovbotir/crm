# Model Creation Checklist

When creating a new Eloquent model for the CRM:

## 1. Generate the Model
```bash
php artisan make:model ModelName -mf
```
This creates:
- `app/Models/ModelName.php` - Model class
- `database/migrations/xxxx_create_model_names_table.php` - Migration
- `database/factories/ModelNameFactory.php` - Factory for testing

## 2. Define Table Structure (Migration)
Edit the migration file to define columns and relationships.

**Required columns**:
- `id()` - primary key (auto-increment)
- `timestamps()` - created_at, updated_at
- Foreign keys where needed (use `foreignId()`)

Example:
```php
Schema::create('contacts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

## 3. Define Model Properties & Methods
In the model class:
- List fillable attributes: `protected $fillable = ['name', 'email'];`
- Define relationships (hasMany, belongsTo, etc.)
- Add custom accessors/mutators if needed

Example:
```php
class Contact extends Model
{
    protected $fillable = ['name', 'email', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 4. Create Factory (for seeding & testing)
Edit `database/factories/ModelNameFactory.php` to define fake data generation.

Example:
```php
public function definition(): array
{
    return [
        'name' => $this->faker->name(),
        'email' => $this->faker->unique()->safeEmail(),
        'user_id' => User::factory(),
    ];
}
```

## 5. Run Migration
```bash
php artisan migrate
```

## 6. Create Tests
Add unit and feature tests in `tests/Unit/Models/` and `tests/Feature/`.
